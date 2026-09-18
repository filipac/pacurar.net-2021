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
        global $wpdb;
        $overview = [];
        foreach (self::MEASUREMENTS as $topic => [$label, $keys]) {
            $item = ['topic' => $topic, 'label' => $label, 'metric' => null, 'change' => null];
            // Measurement dates, not edit/import times, define what is current.
            // Scan compact summaries in small batches, stopping at the previous
            // available day for the selected metric. Never load historical series.
            $cursorDate = '9999-12-31'; $cursorId = PHP_INT_MAX;
            while (true) {
                $rows = $wpdb->get_results($wpdb->prepare("SELECT p.ID, d.meta_value AS day, s.meta_value AS summary
                    FROM {$wpdb->posts} p
                    JOIN {$wpdb->postmeta} d ON d.post_id = p.ID AND d.meta_key = '_health_date'
                    JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
                    JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'health_category'
                    JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug = %s
                    LEFT JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = %s
                    WHERE p.post_type = 'health_entry' AND p.post_status = 'publish'
                    AND (d.meta_value < %s OR (d.meta_value = %s AND p.ID < %d))
                    ORDER BY d.meta_value DESC, p.ID DESC LIMIT 20", $topic, DailySummary::META, $cursorDate, $cursorDate, $cursorId));
                foreach ($rows as $row) {
                    $cursorDate = $row->day; $cursorId = (int) $row->ID;
                    if (! Trends::validDate($row->day)) continue;
                    $summary = is_string($row->summary) ? unserialize($row->summary, ['allowed_classes' => false]) : null;
                    if (! DailySummary::valid($summary, $topic, $row->day)) $summary = DailySummary::legacy($cursorId, $topic, $row->day);
                    if (! DailySummary::valid($summary, $topic, $row->day)) continue;
                    if ($item['metric'] && $row->day >= $item['date']) continue;
                    foreach ($item['metric'] ? [$item['metric']['key']] : $keys as $key) {
                        $definition = MetricCatalog::all()[$key];
                        $metric = $summary['metrics'][$key.'|daily'] ?? null;
                        if (! $metric || $metric['unit'] !== $definition['unit']) continue;
                        if ($item['metric']) {
                            $difference = round($item['metric']['value'] - $metric['value'], 2);
                            $item['change'] = ['date' => $row->day, 'value' => $difference,
                                'text' => ($difference > 0 ? '+' : '').Presentation::number($difference).' '.$metric['unit']];
                            break 3;
                        }
                        $item += ['date' => $row->day, 'url' => get_permalink($cursorId)];
                        $item['metric'] = ['key' => $key, 'value' => $metric['value'], 'unit' => $definition['unit'], 'label' => $definition['label'], 'source' => $definition['source']];
                        break;
                    }
                }
                if (count($rows) < 20) break;
            }
            $overview[] = $item;
        }

        return $overview;
    }
}
