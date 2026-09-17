<?php
// Runs inside integration.php's disposable WordPress database.
$app['config']->set('health.per_page', 2);
$urls = (new App\Health\CacheInvalidator)->urls($id);
$archive = get_post_type_archive_link('health_entry');
$pages = (int) ceil(wp_count_posts('health_entry')->publish / 2);
check(in_array(trailingslashit($archive).'page/'.$pages.'/', $urls, true), 'Last archive page is purged');
$largeJournal = static function ($counts, $type) {
    if ($type === 'health_entry') { $counts = clone $counts; $counts->publish = 41; }
    return $counts;
};
add_filter('wp_count_posts', $largeJournal, 10, 2);
check(in_array(trailingslashit($archive).'page/21/', (new App\Health\CacheInvalidator)->urls($id), true), 'Archive purge goes beyond W3TC default ten-page limit');
remove_filter('wp_count_posts', $largeJournal, 10);
check(in_array(add_query_arg(['topic' => 'heart', 'source' => 'oura'], $archive), $urls, true), 'Combined topic and source filter purged');
check(in_array(add_query_arg(['topic' => '', 'source' => 'oura'], $archive), $urls, true), 'Filter form empty select values supported');
check(in_array(add_query_arg('source', 'oura', $archive), $urls, true), 'Compact source filter purged');
check(! in_array(get_permalink($appleId), $urls, true), 'Other health single pages stay cached');
check(count($urls) === count(array_unique($urls)), 'Purge URLs deduplicated');
$app['config']->set('health.per_page', 18);

update_option('permalink_structure', '');
$wp_rewrite->init();
$app['config']->set('health.per_page', 2);
$plainUrls = (new App\Health\CacheInvalidator)->urls($id);
check(in_array(add_query_arg('paged', 2, get_post_type_archive_link('health_entry')), $plainUrls, true), 'Plain permalink archive pagination supported');
update_option('permalink_structure', '/%postname%/');
$wp_rewrite->init();
$app['config']->set('health.per_page', 18);

// Use the installed plugin's real eligibility function without booting it.
require_once $wpRoot.'wp-content/plugins/w3-total-cache/Util_Environment.php';
$w3Config = new class { public function get_boolean($key): bool { return true; } };
$healthPost = get_post($id);
$ordinaryPost = clone $healthPost; $ordinaryPost->post_type = 'post';
App\Health\CacheInvalidator::write(function () use ($healthPost, $ordinaryPost, $w3Config) {
    foreach (['posts', 'pgcache', 'varnish', 'objectcache'] as $module) {
        check(! W3TC\Util_Environment::is_flushable_post($healthPost, $module, $w3Config), 'Broad W3TC '.$module.' purge suppressed for health transaction');
        check(W3TC\Util_Environment::is_flushable_post($ordinaryPost, $module, $w3Config), 'Ordinary post '.$module.' invalidation preserved');
    }
    check(W3TC\Util_Environment::is_flushable_post($healthPost, 'dbcache', $w3Config), 'Database query invalidation remains enabled');
});
check(W3TC\Util_Environment::is_flushable_post($healthPost, 'objectcache', $w3Config), 'W3TC automatic object purge restored outside health transaction');
try {
    App\Health\CacheInvalidator::write(static fn () => throw new RuntimeException('Fixture write failure.'));
} catch (RuntimeException) {}
check(has_filter('w3tc_flushable_post') === false, 'W3TC filter removed even when writing fails');
