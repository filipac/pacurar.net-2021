<?php

namespace App\Health;

/** Small derived metadata: no raw samples, credentials or arbitrary provider fields. */
class DailySummary
{
    public const META = '_health_daily_summary';
    public const VERSION = 1;

    public static function make(array $entry): array
    {
        $metrics = [];
        Trends::collect($metrics, $entry, 0);
        $summary = ['version' => self::VERSION, 'date' => $entry['date'] ?? null, 'topic' => $entry['topic'] ?? null, 'metrics' => []];
        foreach ($metrics as $key => $metric) {
            $day = $metric['days'][$entry['date']];
            $summary['metrics'][$key] = ['value' => $metric['unit'] === 'timestamp' ? $day['latest'] : $day['sum'] / $day['count'],
                'unit' => $metric['unit'], 'count' => $day['count'], 'method' => $metric['method']];
        }
        return $summary;
    }

    public static function sync($metaId, $postId, $key, $value): void
    {
        if ($key !== '_health_data' || get_post_type($postId) !== 'health_entry') return;
        if (is_array($value)) update_post_meta($postId, self::META, self::make($value));
        else delete_post_meta($postId, self::META);
    }

    public static function remove($metaIds, $postId, $key): void
    {
        if ($key === '_health_data') delete_post_meta($postId, self::META);
    }

    public static function valid(mixed $summary, string $topic, string $date): bool
    {
        return is_array($summary) && ($summary['version'] ?? null) === self::VERSION
            && ($summary['topic'] ?? null) === $topic && ($summary['date'] ?? null) === $date && is_array($summary['metrics'] ?? null);
    }

    public static function legacy(int $postId, string $topic, string $date): array
    {
        global $wpdb;
        // Share the publisher's topic/date lock: a first-view backfill must not
        // overwrite a newer summary while an import is updating this entry.
        $lock = 'health:'.substr(hash('sha256', $wpdb->prefix.$topic.$date), 0, 50);
        $locked = (int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) === 1;
        try {
            // Read directly to avoid filling WP's meta cache with historical ECGs.
            $raw = $wpdb->get_var($wpdb->prepare("SELECT m.meta_value FROM {$wpdb->postmeta} m
                JOIN {$wpdb->posts} p ON p.ID = m.post_id
                WHERE m.post_id = %d AND m.meta_key = '_health_data'
                AND p.post_type = 'health_entry' AND p.post_status = 'publish' LIMIT 1", $postId));
            $entry = is_string($raw) ? unserialize($raw, ['allowed_classes' => false]) : null;
            $summary = is_array($entry) ? self::make($entry) : [];
            if ($locked && self::valid($summary, $topic, $date)) {
                update_post_meta($postId, self::META, $summary);
                wp_cache_delete($postId, 'post_meta');
            }
            return $summary;
        } finally {
            if ($locked) $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
        }
    }
}
