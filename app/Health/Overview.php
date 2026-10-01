<?php

namespace App\Health;

class Overview
{
    private const RECENT_DAYS = 7;
    private const BASELINE_DAYS = 60;

    private const MEASUREMENTS = [
        'weight' => ['Weight', ['withings.measure.1', 'apple_health.weight_body_mass']],
        'sleep' => ['Sleep', ['oura.daily_sleep.score', 'apple_health.sleep_analysis.totalSleep', 'oura.sleep.total_sleep_duration', 'withings.sleep.asleepduration']],
        'activity' => ['Activity', ['oura.daily_activity.steps', 'apple_health.step_count', 'withings.activity.steps']],
        'heart' => ['HRV', ['apple_health.heart_rate_variability_rmssd']],
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
            if ($item['metric']) {
                if ($item['change']) $item['change']['tone'] = self::tone($item['metric']['key'], $item['change']['value'] <=> 0);
                $item['baseline'] = self::baseline($topic, $item['metric']['key'], $item['date']);
                $item['stale_days'] = self::date($item['date'])->diff(self::date('today'))->days;
            }
            $overview[] = $item;
        }

        return $overview;
    }

    /**
     * Your own normal: the last 7 days' average against the 53 days before them.
     * A single day is noisy (HRV can swing 50% overnight); a weekly average is not.
     */
    private static function baseline(string $topic, string $key, string $date): ?array
    {
        global $wpdb;
        $end = self::date($date);
        $rows = $wpdb->get_results($wpdb->prepare("SELECT p.ID, d.meta_value AS day, s.meta_value AS summary
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} d ON d.post_id = p.ID AND d.meta_key = '_health_date'
            JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
            JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'health_category'
            JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug = %s
            LEFT JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = %s
            WHERE p.post_type = 'health_entry' AND p.post_status = 'publish' AND d.meta_value BETWEEN %s AND %s",
            $topic, DailySummary::META, $end->modify('-'.(self::BASELINE_DAYS - 1).' days')->format('Y-m-d'), $date));
        $unit = MetricCatalog::all()[$key]['unit'];
        $recentFrom = $end->modify('-'.(self::RECENT_DAYS - 1).' days')->format('Y-m-d');
        $recent = []; $history = [];
        foreach ($rows as $row) {
            if (! Trends::validDate($row->day)) continue;
            $summary = is_string($row->summary) ? unserialize($row->summary, ['allowed_classes' => false]) : null;
            if (! DailySummary::valid($summary, $topic, $row->day)) $summary = DailySummary::legacy((int) $row->ID, $topic, $row->day);
            if (! DailySummary::valid($summary, $topic, $row->day)) continue;
            $metric = $summary['metrics'][$key.'|daily'] ?? null;
            if (! $metric || $metric['unit'] !== $unit) continue;
            if ($row->day >= $recentFrom) $recent[$row->day] = $metric['value'];
            else $history[$row->day] = $metric['value'];
        }
        // Too few readings make a misleading "normal"; fall back to the plain change.
        if (count($recent) < 3 || count($history) < 14) return null;
        $average = array_sum($recent) / count($recent);
        $mean = array_sum($history) / count($history);
        $sd = sqrt(array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $history)) / count($history));
        $low = $mean - $sd; $high = $mean + $sd;
        $position = $average > $high ? 1 : ($average < $low ? -1 : 0);

        return ['average' => $average, 'low' => $low, 'high' => $high, 'days' => count($recent), 'history_days' => count($history),
            'status' => [1 => 'Above your normal', 0 => 'Within your normal', -1 => 'Below your normal'][$position],
            'tone' => self::tone($key, $position)];
    }

    /** good, bad or neutral for a movement in this direction, from the catalog's "better". */
    public static function tone(string $key, int $direction): string
    {
        $better = MetricCatalog::all()[$key]['better'] ?? null;
        if (! $direction || ! $better) return 'neutral';

        return ($direction > 0) === ($better === 'up') ? 'good' : 'bad';
    }

    private static function date(string $date): \DateTimeImmutable
    {
        return new \DateTimeImmutable($date, new \DateTimeZone('Europe/Bucharest'));
    }
}
