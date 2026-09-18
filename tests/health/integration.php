<?php
/** Standalone, isolated WordPress integration suite. No PHPUnit dependency.
 * php tests/health/integration.php
 * Creates and drops only a new health_journal_test_* database; never modifies blog tables.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
register_shutdown_function(function () { $e = error_get_last(); if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR])) fwrite(STDERR, $e['message']."\n".$e['file'].':'.$e['line']."\n"); });
$healthThemeRoot = dirname(__DIR__, 2);
$wpRoot = dirname($healthThemeRoot, 3).'/';
if (($argv[1] ?? '') !== 'child') {
    define('SHORTINIT', true);
    require $wpRoot.'wp-load.php';
    $database = 'health_journal_test_'.bin2hex(random_bytes(6));
    $directory = sys_get_temp_dir().'/'.$database;
    mkdir($directory, 0700);
    $configuration = ['database' => $database, 'user' => DB_USER, 'password' => DB_PASSWORD, 'host' => DB_HOST, 'directory' => $directory];
    file_put_contents($directory.'/config.json', json_encode($configuration, JSON_THROW_ON_ERROR));
    chmod($directory.'/config.json', 0600);
    if ($wpdb->query("CREATE DATABASE `$database` CHARACTER SET utf8mb4") === false) throw new RuntimeException('Cannot create isolated health test database.');
    try {
        $process = proc_open([PHP_BINARY, __FILE__, 'child', $directory.'/config.json'], [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes);
        $exit = proc_close($process);
    } finally {
        $wpdb->query("DROP DATABASE `$database`");
        unlink($directory.'/config.json');
        foreach (glob($directory.'/*') as $path) if (is_file($path)) unlink($path);
        @rmdir($directory);
    }
    exit($exit);
}
$config = json_decode(file_get_contents($argv[2]), true, flags: JSON_THROW_ON_ERROR);
if (! preg_match('/^health_journal_test_[a-f0-9]{12}$/D', $config['database'])) throw new RuntimeException('Unsafe database name.');
define('DB_NAME', $config['database']); define('DB_USER', $config['user']); define('DB_PASSWORD', $config['password']); define('DB_HOST', $config['host']);
define('DB_CHARSET', 'utf8mb4'); define('DB_COLLATE', ''); define('ABSPATH', $wpRoot);
define('WP_INSTALLING', true); define('WP_DEBUG', false); define('WP_CACHE', false); define('DISABLE_WP_CRON', true);
define('WP_CONTENT_DIR', $config['directory']); define('WP_CONTENT_URL', 'https://health-fixture.invalid/content');
define('WP_PLUGIN_DIR', $config['directory'].'/plugins'); define('WPMU_PLUGIN_DIR', $config['directory'].'/mu-plugins');
$_SERVER['HTTP_HOST'] = 'health-fixture.invalid'; $_SERVER['REQUEST_URI'] = '/'; $_SERVER['HTTPS'] = 'on';
$table_prefix = 'hjt_';
require $wpRoot.'wp-settings.php';
add_filter('pre_wp_mail', '__return_true');
require $wpRoot.'wp-admin/includes/upgrade.php';
wp_install('Health test', 'health_admin', 'fixture@example.invalid', false, '', 'fixture-password');
require $healthThemeRoot.'/vendor/autoload.php';
$app = new Illuminate\Container\Container;
Illuminate\Container\Container::setInstance($app);
$app->instance('config', new Illuminate\Config\Repository(['health' => require $healthThemeRoot.'/config/health.php']));
// Record URL purges without loading W3TC or touching real theme caches.
$GLOBALS['healthPurgedUrls'] = [];
if (! function_exists('w3tc_flush_url')) {
    function w3tc_flush_url($url): void { $GLOBALS['healthPurgedUrls'][] = $url; }
}
$cacheInvalidator = new class extends App\Health\CacheInvalidator {
    public int $calls = 0;
    public bool $fail = false;
    public function flush(int $postId): void {
        $this->calls++;
        if ($this->fail) throw new RuntimeException('Fixture purge failure.');
        parent::flush($postId);
    }
};
$app->instance(App\Health\CacheInvalidator::class, $cacheInvalidator);
// A global flush command is never needed during health publishing.
$app->instance(Illuminate\Contracts\Console\Kernel::class, new class {
    public function call($command): int { throw new RuntimeException('Unexpected global cache command.'); }
});
update_option('permalink_structure', '/%postname%/');
$wp_rewrite->init();
wp_cache_set('unrelated-page', 'keep', 'health-test');
$provider = new App\Providers\HealthJournalProvider($app);
$provider->boot(); $provider->registerContent();
$server = rest_get_server();
$admin = get_user_by('login', 'health_admin');
$userId = wp_insert_user(['user_login' => 'fixture_publisher', 'user_pass' => 'fixture-password', 'role' => 'health_publisher']);
$subscriber = wp_insert_user(['user_login' => 'fixture_subscriber', 'user_pass' => 'fixture-password', 'role' => 'subscriber']);
$count = 0;
function check($condition, string $message): void { global $count; if (! $condition) throw new RuntimeException($message); $count++; echo "PASS $message\n"; }
function request(string $method, array $params) {
    $request = new WP_REST_Request($method, '/pacurar2020/v1/health-entries');
    if ($method === 'POST') { $request->set_header('content-type', 'application/json'); $request->set_body(json_encode($params)); }
    else $request->set_query_params($params);
    return rest_do_request($request);
}
$entry = ['schema_version' => 1, 'topic' => 'weight', 'date' => '2001-01-01', 'timezone' => 'Europe/Bucharest', 'expected_revision' => null, 'providers' => ['withings' => ['fetched_at' => '2001-01-01T10:00:00+02:00', 'metrics' => [['key' => 'withings.measure.1', 'label' => 'Weight', 'unit' => 'kg', 'value' => 80, 'at' => '2001-01-01T08:00:00+02:00']], 'series' => []]]];
wp_set_current_user(0); check(request('POST', $entry)->get_status() === 401, 'Anonymous write rejected');
wp_set_current_user($subscriber); check(request('POST', $entry)->get_status() === 403, 'Subscriber write rejected');
wp_set_current_user($userId);
check(! current_user_can('edit_posts') && current_user_can('publish_health_entries'), 'Publisher restricted to health entries');
$bad = $entry; $bad['providers']['withings']['device_id'] = 'private';
check(request('POST', $bad)->get_status() === 422, 'Private provider fields rejected');
$bad = $entry; $bad['providers']['withings']['metrics'][0]['key'] = 'withings.measure.999';
check(request('POST', $bad)->get_status() === 422, 'Unknown metrics rejected');
$created = request('POST', $entry);
check($created->get_status() === 200 && ($created->get_data()['operation'] ?? '') === 'created', 'Restricted publisher creates entry');
check($cacheInvalidator->calls === 1, 'Successful create performs a targeted URL purge');
$id = $created->get_data()['id'];
check(! isset($created->get_data()['cache_warning']), 'Health publishing does not invoke global Laravel cache commands');
check(in_array(get_permalink($id), $GLOBALS['healthPurgedUrls'], true), 'Changed single page purged');
check(in_array(get_post_type_archive_link('health_entry'), $GLOBALS['healthPurgedUrls'], true), 'Health archive purged');
check(in_array(home_url('/health/compare'), $GLOBALS['healthPurgedUrls'], true), 'Comparison landing page purged');
check(! in_array(home_url('/'), $GLOBALS['healthPurgedUrls'], true), 'Blog home is not purged');
check(wp_cache_get('unrelated-page', 'health-test') === 'keep', 'Unrelated WordPress cached objects survive publication');
check(has_filter('w3tc_flushable_post') === false, 'Automatic W3TC purge behavior restored after write');
check(request('POST', $entry)->get_data()['id'] === $id, 'Repeated create returns same post');
check(request('POST', $entry)->get_data()['operation'] === 'unchanged', 'Retry after lost response is unchanged');
check($cacheInvalidator->calls === 1, 'Unchanged retries do not flush caches');
check(wp_get_object_terms($id, 'health_category', ['fields' => 'slugs']) === ['weight'], 'Topic taxonomy assigned');
check(wp_get_object_terms($id, 'health_source', ['fields' => 'slugs']) === ['withings'], 'Source taxonomy assigned');
$read = request('GET', ['topic' => 'weight', 'date' => '2001-01-01'])->get_data();
$updated = $entry; $updated['providers']['withings']['metrics'][0]['value'] = 79;
check(request('POST', $updated)->get_status() === 409, 'Stale revision rejected');
check($cacheInvalidator->calls === 1, 'Rejected updates do not flush caches');
$updated['expected_revision'] = $read['revision'];
check(request('POST', $updated)->get_data()['operation'] === 'updated', 'Current revision updates existing entry');
check($cacheInvalidator->calls === 2, 'Successful update clears caches');
check(request('POST', $updated)->get_data()['operation'] === 'unchanged', 'Update retry is idempotent');
wp_set_current_user(0);
$public = rest_do_request(new WP_REST_Request('GET', '/wp/v2/health-entries/'.$id));
check($public->get_status() === 200 && $public->get_data()['health_data']['providers']['withings']['metrics'][0]['value'] === 79.0, 'Public REST exposes normalized measurements');
check(! str_contains(json_encode($public->get_data()), 'device_id'), 'Public REST contains no private provider field');
wp_set_current_user($userId);
$otherUser = wp_insert_user(['user_login' => 'other_publisher', 'user_pass' => 'fixture-password', 'role' => 'health_publisher']);
wp_set_current_user($otherUser);
check(request('POST', $updated)->get_status() === 403, 'Other publisher cannot overwrite author');
wp_set_current_user($userId);
$heart = $entry; $heart['topic'] = 'heart'; $heart['providers']['withings']['metrics'][0] = ['key' => 'withings.measure.11', 'value' => 70, 'at' => '2001-01-01T08:00:00+02:00'];
$heartResult = request('POST', $heart)->get_data();
$heart['expected_revision'] = $heartResult['revision'];
$heart['providers']['oura'] = ['fetched_at' => '2001-01-01T10:00:00+02:00', 'metrics' => [['key' => 'oura.sleep.average_heart_rate', 'value' => 60, 'at' => '2001-01-01T08:00:00+02:00']], 'series' => []];
check(request('POST', $heart)->get_data()['operation'] === 'updated', 'Two providers share one topic entry');
$heart['expected_revision'] = request('GET', ['topic' => 'heart', 'date' => '2001-01-01'])->get_data()['revision'];
unset($heart['providers']['withings']); $heart['providers']['oura']['metrics'][0]['value'] = 61;
check(request('POST', $heart)->get_status() === 409, 'Partial update cannot silently erase another provider');
$read = request('GET', ['topic' => 'weight', 'date' => '2001-01-01'])->get_data();
wp_update_post(['ID' => $id, 'post_title' => 'Manual edit']);
$updated['expected_revision'] = $read['revision']; $updated['providers']['withings']['metrics'][0]['value'] = 78;
check(request('POST', $updated)->get_status() === 409, 'Manual WordPress edit invalidates preview');

// Apple Health uses the same strict contract, role, topic/date identity and public field.
$apple = ['schema_version' => 1, 'topic' => 'activity', 'date' => '2001-01-04', 'timezone' => 'Europe/Bucharest', 'expected_revision' => null,
    'providers' => ['apple_health' => ['fetched_at' => '2001-01-04T10:00:00+02:00', 'metrics' => [], 'series' => [], 'workouts' => [
        ['type' => 'outdoor-run', 'origin' => 'apple_health', 'start' => '2001-01-04T08:00:00+02:00', 'end' => '2001-01-04T08:10:00+02:00',
            'metrics' => [['key' => 'apple_health.workout.distance', 'label' => 'Private custom title', 'value' => 1.5, 'unit' => 'private unit', 'at' => '2001-01-04T08:00:00+02:00']],
            'series' => [['key' => 'apple_health.workout.heartRateData.Avg', 'points' => [['at' => '2001-01-04T08:01:00+02:00', 'value' => 120]]]]]]]]];
$createdApple = request('POST', $apple);
check($createdApple->get_status() === 200 && $createdApple->get_data()['operation'] === 'created', 'Apple Health workout-only entry accepted');
$appleId = $createdApple->get_data()['id'];
check(wp_get_object_terms($appleId, 'health_source', ['fields' => 'slugs']) === ['apple_health'], 'Apple Health source taxonomy assigned');
check(request('POST', $apple)->get_data()['operation'] === 'unchanged', 'Apple Health repeated publish does not duplicate');
$badApple = $apple; $badApple['providers']['apple_health']['workouts'][0]['route'] = [['latitude' => 44.1]];
check(request('POST', $badApple)->get_status() === 422, 'Workout routes rejected');
$badApple = $apple; $badApple['providers']['apple_health']['workouts'][0]['type'] = 'Private arbitrary text';
check(request('POST', $badApple)->get_status() === 422, 'Workout type must be an allowed category');
$badApple = $apple; $badApple['providers']['apple_health']['workouts'][0]['origin'] = 'Private watch name';
check(request('POST', $badApple)->get_status() === 422, 'Private workout device names rejected');
wp_set_current_user(0);
$publicApple = rest_do_request(new WP_REST_Request('GET', '/wp/v2/health-entries/'.$appleId));
check($publicApple->get_status() === 200 && $publicApple->get_data()['health_data']['providers']['apple_health']['workouts'][0]['metrics'][0]['label'] === 'Distance', 'Public Apple metrics use catalog labels');
check(! str_contains(json_encode($publicApple->get_data()), 'Private'), 'Private export text never appears in public REST');
wp_set_current_user($userId);
$apple['expected_revision'] = $createdApple->get_data()['revision'];
$apple['providers']['apple_health']['workouts'][0]['metrics'][0]['value'] = 1.6;
check(request('POST', $apple)->get_data()['operation'] === 'updated', 'Apple Health correction updates same entry');
$ouraOnly = $apple; $ouraOnly['date'] = '2001-01-05'; $ouraOnly['expected_revision'] = null;
$ouraOnly['providers'] = ['oura' => ['fetched_at' => '2001-01-05T10:00:00+02:00', 'metrics' => [['key' => 'oura.workout.duration', 'value' => 600, 'at' => '2001-01-05T08:00:00+02:00']], 'series' => []]];
$ouraCreated = request('POST', $ouraOnly)->get_data();
$richer = $ouraOnly; $richer['expected_revision'] = $ouraCreated['revision']; $richer['providers']['apple_health'] = $apple['providers']['apple_health'];
$richer['providers']['apple_health']['workouts'][0]['origin'] = 'oura';
$richer['providers']['apple_health']['workouts'][0]['start'] = '2001-01-05T08:00:00+02:00';
$richer['providers']['apple_health']['workouts'][0]['end'] = '2001-01-05T08:10:00+02:00';
check(request('POST', $richer)->get_data()['operation'] === 'updated', 'Richer exported Oura workout replaces duplicate API scalars');
check(wp_get_object_terms($ouraCreated['id'], 'health_source', ['fields' => 'slugs']) === ['apple_health'], 'Deduplicated source terms match retained workout data');

$cacheInvalidator->fail = true;
$cacheFailure = $entry; $cacheFailure['date'] = '2001-01-06';
$savedWithWarning = request('POST', $cacheFailure)->get_data();
check($savedWithWarning['operation'] === 'created' && isset($savedWithWarning['cache_warning']), 'Cache failure reports saved entry with warning instead of failed write');
check(request('GET', ['topic' => 'weight', 'date' => '2001-01-06'])->get_data()['entry']['date'] === '2001-01-06', 'Cache failure does not roll back committed data');
$cacheInvalidator->fail = false;

$mindful = ['schema_version' => 1, 'topic' => 'mindfulness', 'date' => '2001-01-07', 'timezone' => 'Europe/Bucharest', 'expected_revision' => null,
    'providers' => ['apple_health' => ['fetched_at' => '2001-01-07T10:00:00+02:00', 'metrics' => [
        ['key' => 'apple_health.mindful_minutes', 'value' => 12.5, 'at' => '2001-01-07T08:00:00+02:00']], 'series' => []]]];
$mindfulCreated = request('POST', $mindful)->get_data();
check(($mindfulCreated['operation'] ?? null) === 'created', 'Apple Health mindful minutes accepted');
check(wp_get_object_terms($mindfulCreated['id'], 'health_category', ['fields' => 'slugs']) === ['mindfulness'], 'Mindful minutes assigned to mindfulness topic');
wp_set_current_user(0);
$publicMindful = rest_do_request(new WP_REST_Request('GET', '/wp/v2/health-entries/'.$mindfulCreated['id']))->get_data();
$metric = $publicMindful['health_data']['providers']['apple_health']['metrics'][0];
check($metric['value'] === 12.5 && $metric['unit'] === 'min' && $metric['label'] === 'Mindful minutes', 'Public mindful minutes expose normalized value, label and unit');
wp_set_current_user($userId);
check(request('POST', $mindful)->get_data()['operation'] === 'unchanged', 'Mindful minutes retry is unchanged');

// Concurrent requests use independent database connections and PHP processes.
$fixtureFile = $config['directory'].'/concurrent.json';
file_put_contents($fixtureFile, json_encode($entry + ['unused' => false]));
$worker = $config['directory'].'/worker.php';
$workerCode = <<<'WORKER'
<?php
$config = json_decode(file_get_contents($argv[1]), true);
foreach (['DB_NAME' => $config['database'], 'DB_USER' => $config['user'], 'DB_PASSWORD' => $config['password'], 'DB_HOST' => $config['host'], 'DB_CHARSET' => 'utf8mb4', 'DB_COLLATE' => '', 'ABSPATH' => $argv[2], 'WP_CONTENT_DIR' => $config['directory'], 'WP_CONTENT_URL' => 'https://health-fixture.invalid/content', 'WP_PLUGIN_DIR' => $config['directory'].'/plugins', 'WPMU_PLUGIN_DIR' => $config['directory'].'/mu-plugins', 'WP_INSTALLING' => true, 'WP_DEBUG' => false, 'WP_CACHE' => false, 'DISABLE_WP_CRON' => true] as $key => $value) define($key, $value);
$table_prefix = 'hjt_'; $_SERVER['HTTP_HOST'] = 'health-fixture.invalid'; $_SERVER['REQUEST_URI'] = '/'; $_SERVER['HTTPS'] = 'on';
require ABSPATH.'wp-settings.php'; require $argv[3].'/vendor/autoload.php';
$app = new Illuminate\Container\Container; Illuminate\Container\Container::setInstance($app); $app->instance('config', new Illuminate\Config\Repository(['health' => require $argv[3].'/config/health.php']));
$app->instance(Illuminate\Contracts\Console\Kernel::class, new class { public function call($command): int { return 0; } });
$provider = new App\Providers\HealthJournalProvider($app); $provider->boot(); $provider->registerContent();
wp_set_current_user((int) $argv[4]);
$entry = json_decode(file_get_contents($config['directory'].'/concurrent.json'), true); unset($entry['unused']); $entry['date'] = '2001-01-02';
$r = new WP_REST_Request('POST', '/pacurar2020/v1/health-entries'); $r->set_header('content-type', 'application/json'); $r->set_body(json_encode($entry));
$result = rest_do_request($r); echo json_encode(['status' => $result->get_status(), 'result' => $result->get_data()]);
WORKER;
file_put_contents($worker, $workerCode);
$processes = [];
for ($i = 0; $i < 3; $i++) {
    $p = proc_open([PHP_BINARY, $worker, $argv[2], $wpRoot, $healthThemeRoot, (string) $userId], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    fclose($pipes[0]); $processes[] = [$p, $pipes];
}
$ids = [];
foreach ($processes as [$p, $pipes]) { $out = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]); $exit = proc_close($p); $data = json_decode($out, true); check($exit === 0 && ($data['status'] ?? null) === 200, 'Concurrent request succeeds'); $ids[] = $data['result']['id']; }
check(count(array_unique($ids)) === 1, 'Concurrent creates produce exactly one post');
require __DIR__.'/cache.php';
require __DIR__.'/trends.php';
foreach ([['cycling_distance', 'activity', 'Cycling distance', 'km', 12.5], ['waist_circumference', 'body-composition', 'Waist circumference', 'cm', 82.0]] as [$name, $topic, $label, $unit, $value]) {
    wp_set_current_user($userId);
    $newMetric = ['schema_version' => 1, 'topic' => $topic, 'date' => '2001-01-08', 'timezone' => 'Europe/Bucharest', 'expected_revision' => null,
        'providers' => ['apple_health' => ['fetched_at' => '2001-01-08T10:00:00+02:00', 'series' => [], 'metrics' => [
            ['key' => 'apple_health.'.$name, 'value' => $value, 'at' => '2001-01-08T08:00:00+02:00'],
        ]]]];
    $createdMetric = request('POST', $newMetric)->get_data();
    check(($createdMetric['operation'] ?? null) === 'created', $name.' accepted by publishing contract');
    check(wp_get_object_terms($createdMetric['id'], 'health_category', ['fields' => 'slugs']) === [$topic], $name.' assigned to correct topic');
    wp_set_current_user(0);
    $publicMetric = rest_do_request(new WP_REST_Request('GET', '/wp/v2/health-entries/'.$createdMetric['id']))->get_data()['health_data']['providers']['apple_health']['metrics'][0];
    check($publicMetric['label'] === $label && $publicMetric['unit'] === $unit && $publicMetric['value'] === $value, $name.' exposes normalized public values');
    $trendMetric = App\Health\Trends::published()['apple_health.'.$name.'|daily'];
    check($trendMetric['days']['2001-01-08']['value'] === $value && $trendMetric['unit'] === $unit, $name.' available in comparison charts');
    wp_set_current_user($userId);
    check(request('POST', $newMetric)->get_data()['operation'] === 'unchanged', $name.' repeated publication does not duplicate data');
}
require __DIR__.'/overview.php';
require __DIR__.'/comparisons.php';
require __DIR__.'/timeline.php';
echo "\n$count integration checks passed; isolated database will be removed.\n";
