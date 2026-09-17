<?php

namespace App\Health;

class CacheInvalidator
{
    public static function write(callable $write): mixed
    {
        // W3TC normally purges the whole object cache and unrelated index pages
        // on save_post. WordPress already invalidates this post's objects; our
        // URL purge runs after the health metadata transaction has committed.
        $filter = static function ($flushable, $post, $module) {
            if (($post->post_type ?? null) === 'health_entry' && in_array($module, ['posts', 'pgcache', 'varnish', 'objectcache'], true)) return false;
            return $flushable;
        };
        add_filter('w3tc_flushable_post', $filter, PHP_INT_MAX, 3);
        try {
            return $write();
        } finally {
            remove_filter('w3tc_flushable_post', $filter, PHP_INT_MAX);
        }
    }

    public function flush(int $postId): void
    {
        if (! function_exists('w3tc_flush_url')) return;

        foreach ($this->urls($postId) as $url) w3tc_flush_url($url);
        // Health views read live WordPress data. Compiled Blade templates and
        // unrelated Laravel cache entries do not need to be cleared on writes.
    }

    public function urls(int $postId): array
    {
        global $wp_rewrite;
        $archive = get_post_type_archive_link('health_entry');
        $urls = [get_permalink($postId)];
        if (! $archive) return array_filter($urls);

        $count = (int) (wp_count_posts('health_entry')->publish ?? 0);
        $pages = max(1, (int) ceil($count / max(1, (int) config('health.per_page', 18))));
        $filters = [[]];
        foreach (['', ...array_keys(MetricCatalog::TOPICS)] as $topic) {
            foreach (['', ...array_keys(MetricCatalog::SOURCES)] as $source) {
                // Include the form's empty select values and compact links.
                $filters[] = compact('topic', 'source');
                $filters[] = array_filter(compact('topic', 'source'), static fn ($value) => $value !== '');
            }
        }
        for ($page = 1; $page <= $pages; $page++) {
            $url = $page === 1 ? $archive : ($wp_rewrite->using_permalinks()
                ? trailingslashit($archive).user_trailingslashit($wp_rewrite->pagination_base.'/'.$page, 'paged')
                : add_query_arg('paged', $page, $archive));
            foreach ($filters as $filter) $urls[] = add_query_arg($filter, $url);
        }
        $urls[] = home_url('/'.trim(config('health.archive_slug', 'health'), '/').'/compare');

        return array_values(array_unique(array_filter($urls)));
    }
}
