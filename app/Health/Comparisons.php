<?php

namespace App\Health;

/** One bounded query per rendered page, then all metric comparisons in memory. */
class Comparisons
{
    private array $days = [];
    private array $results = [];

    public function __construct(array $entries)
    {
        $dates = []; $topics = [];
        foreach ($entries as $entry) {
            if (! is_array($entry) || ! Trends::validDate($entry['date'] ?? null) || ! isset(MetricCatalog::TOPICS[$entry['topic'] ?? ''])) continue;
            $dates[] = $entry['date']; $topics[] = $entry['topic'];
        }
        if (! $dates) return;
        global $wpdb;
        $topics = array_values(array_unique($topics));
        $from = self::date(min($dates))->modify('-7 days')->format('Y-m-d');
        $placeholders = implode(',', array_fill(0, count($topics), '%s'));
        $rows = $wpdb->get_results($wpdb->prepare("SELECT p.ID, d.meta_value AS day, t.slug AS topic, s.meta_value AS summary
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} d ON d.post_id = p.ID AND d.meta_key = '_health_date'
            JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
            JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'health_category'
            JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
            LEFT JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = %s
            WHERE p.post_type = 'health_entry' AND p.post_status = 'publish'
            AND d.meta_value BETWEEN %s AND %s AND t.slug IN ($placeholders)
            ORDER BY p.ID ASC", DailySummary::META, $from, max($dates), ...$topics));
        foreach ($rows as $row) {
            $summary = is_string($row->summary) ? unserialize($row->summary, ['allowed_classes' => false]) : null;
            if (! DailySummary::valid($summary, $row->topic, $row->day)) $summary = DailySummary::legacy((int) $row->ID, $row->topic, $row->day);
            if (DailySummary::valid($summary, $row->topic, $row->day)) $this->days[$row->topic][$row->day] = $summary['metrics'];
        }
    }

    public function metric(string $topic, string $date, string $key): ?array
    {
        $cacheKey = $topic.':'.$date.':'.$key;
        if (array_key_exists($cacheKey, $this->results)) return $this->results[$cacheKey];
        $current = $this->days[$topic][$date][$key] ?? null;
        if (! $current) return $this->results[$cacheKey] = null;
        $deltas = []; $days = []; $unit = $current['unit'];
        foreach ([1, 7] as $offset) {
            $previousDate = self::date($date)->modify('-'.$offset.' days')->format('Y-m-d');
            $previous = $this->days[$topic][$previousDate][$key] ?? null;
            if (! $previous || $previous['unit'] !== $unit) continue;
            $difference = Trends::plotValue($current['value'], $date, $unit) - Trends::plotValue($previous['value'], $previousDate, $unit);
            if ($unit === 'timestamp') $difference *= 60;
            $deltaUnit = match ($unit) { 'timestamp' => 'min', '%' => 'pp', default => $unit };
            $difference = round($difference, 2);
            $deltas[] = ['offset' => $offset, 'date' => $previousDate, 'value' => $difference,
                'text' => ($difference > 0 ? '+' : '').Presentation::number($difference).' '.$deltaUnit];
        }
        $from = self::date($date)->modify('-6 days')->format('Y-m-d');
        for ($offset = 6; $offset >= 0; $offset--) {
            $day = self::date($date)->modify('-'.$offset.' days')->format('Y-m-d');
            $reading = $this->days[$topic][$day][$key] ?? null;
            if ($reading && $reading['unit'] === $unit) $days[$day] = ['date' => $day, 'value' => $reading['value']];
        }
        $chart = count($days) > 1 ? Trends::chart(['unit' => $unit, 'days' => $days], $from, $date) : null;
        if (! $deltas && ! $chart) return $this->results[$cacheKey] = null;
        return $this->results[$cacheKey] = compact('deltas', 'chart', 'current', 'from', 'date', 'key');
    }

    private static function date(string $date): \DateTimeImmutable
    {
        return new \DateTimeImmutable($date, new \DateTimeZone('Europe/Bucharest'));
    }
}
