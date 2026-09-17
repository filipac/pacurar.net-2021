<?php

namespace App\Health;

class Overview
{
    private const MEASUREMENTS = [
        'weight' => ['Weight', ['withings.measure.1', 'apple_health.weight_body_mass']],
        'sleep' => ['Sleep', ['oura.daily_sleep.score', 'apple_health.sleep_analysis.totalSleep', 'oura.sleep.total_sleep_duration', 'withings.sleep.asleepduration']],
        'activity' => ['Activity', ['oura.daily_activity.steps', 'apple_health.step_count', 'withings.activity.steps']],
        'recovery' => ['Readiness', ['oura.daily_readiness.score']],
    ];

    public static function latest(): array
    {
        $overview = [];
        foreach (self::MEASUREMENTS as $topic => [$label, $keys]) {
            $item = ['topic' => $topic, 'label' => $label, 'metric' => null];
            // Measurement dates, not edit/import times, define what is current.
            // Read IDs in small batches and payloads one at a time; do not load
            // every historical series just to display four values.
            for ($page = 1; ; $page++) {
                $ids = get_posts(['post_type' => 'health_entry', 'post_status' => 'publish',
                    'fields' => 'ids', 'posts_per_page' => 20, 'paged' => $page,
                    'meta_key' => '_health_date', 'orderby' => ['meta_value' => 'DESC', 'ID' => 'DESC'],
                    'tax_query' => [['taxonomy' => 'health_category', 'field' => 'slug', 'terms' => $topic]],
                    'suppress_filters' => true]);
                foreach ($ids as $id) {
                    $entry = get_post_meta($id, '_health_data', true);
                    if (! is_array($entry) || ! Trends::validDate($entry['date'] ?? null)) continue;
                    foreach ($keys as $key) {
                        $definition = MetricCatalog::all()[$key];
                        $source = $definition['source'];
                        foreach ($entry['providers'][$source]['metrics'] ?? [] as $metric) {
                            if (($metric['key'] ?? null) !== $key || ! is_numeric($metric['value'] ?? null) || ! is_finite((float) $metric['value'])) continue;
                            $item += ['date' => $entry['date'], 'url' => get_permalink($id)];
                            $item['metric'] = ['key' => $key, 'value' => $metric['value'], 'unit' => $definition['unit'], 'label' => $definition['label'], 'source' => $source];
                            break 4;
                        }
                    }
                }
                if (count($ids) < 20) break;
            }
            $overview[] = $item;
        }

        return $overview;
    }
}
