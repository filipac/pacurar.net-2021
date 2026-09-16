<?php

namespace App\Health;

/** Compact daily trends built only from published, catalogued measurements. */
class Trends
{
    public static function published(): array
    {
        global $wpdb;
        $metrics = [];
        $cursor = 0;
        // Read one payload at a time: ECG entries can be several MB. Do not fill
        // WordPress's meta cache with every raw sample in the journal's history.
        while ($row = $wpdb->get_row($wpdb->prepare(
            "SELECT m.meta_id, m.post_id, m.meta_value FROM {$wpdb->postmeta} m
             INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
             WHERE m.meta_key = '_health_data' AND m.meta_id > %d
             AND p.post_type = 'health_entry' AND p.post_status = 'publish'
             ORDER BY m.meta_id ASC LIMIT 1", $cursor
        ))) {
            $cursor = (int) $row->meta_id;
            $entry = unserialize($row->meta_value, ['allowed_classes' => false]);
            if (is_array($entry)) self::collect($metrics, $entry, (int) $row->post_id);
        }
        foreach ($metrics as &$metric) {
            ksort($metric['days']);
            foreach ($metric['days'] as &$day) {
                $day['value'] = $metric['unit'] === 'timestamp' ? $day['latest'] : $day['sum'] / $day['count'];
                unset($day['sum'], $day['latest'], $day['latest_at']);
            }
            unset($day);
        }
        unset($metric);
        uasort($metrics, fn ($a, $b) => strnatcasecmp($a['name'], $b['name']));
        return $metrics;
    }

    public static function collect(array &$metrics, array $entry, int $postId): void
    {
        $date = $entry['date'] ?? '';
        if (! self::validDate($date) || ! isset(MetricCatalog::TOPICS[$entry['topic'] ?? ''])) return;
        foreach ($entry['providers'] ?? [] as $source => $section) {
            if (! isset(MetricCatalog::SOURCES[$source]) || ! is_array($section)) continue;
            self::section($metrics, $section, $source, $entry['topic'], $date, $postId);
            foreach ($section['workouts'] ?? [] as $workout) {
                if (! isset(MetricCatalog::WORKOUT_TYPES[$workout['type'] ?? ''], MetricCatalog::SOURCES[$workout['origin'] ?? ''])) continue;
                self::section($metrics, $workout, $source, $entry['topic'], $date, $postId, $workout['type'].'|'.$workout['origin'], MetricCatalog::WORKOUT_TYPES[$workout['type']].' / '.MetricCatalog::SOURCES[$workout['origin']]);
            }
        }
    }

    private static function section(array &$metrics, array $section, string $source, string $topic, string $date, int $postId, string $context = 'daily', string $contextLabel = ''): void
    {
        foreach (['metrics', 'series'] as $kind) {
            foreach ($section[$kind] ?? [] as $item) {
                $definition = MetricCatalog::all()[$item['key'] ?? ''] ?? null;
                if (! $definition || $definition['source'] !== $source || $definition['topic'] !== $topic || (! empty($definition['series'])) !== ($kind === 'series')) continue;
                $id = $item['key'].'|'.$context;
                foreach ($kind === 'series' ? ($item['points'] ?? []) : [$item] as $point) {
                    if (! is_numeric($point['value'] ?? null) || ! is_finite((float) $point['value'])) continue;
                    $value = (float) $point['value'];
                    $at = strtotime($point['at'] ?? '') ?: 0;
                    $metrics[$id] ??= [
                        'key' => $id, 'label' => $definition['label'], 'unit' => $definition['unit'],
                        'source' => $source, 'topic' => $topic, 'context' => $contextLabel,
                        'dataset' => str_replace('_', ' ', $definition['endpoint']).($kind === 'series' ? ' samples' : ''),
                        'name' => MetricCatalog::TOPICS[$topic].' · '.$definition['label'].' · '.MetricCatalog::SOURCES[$source].' · '.str_replace('_', ' ', $definition['endpoint']).($kind === 'series' ? ' samples' : '').($contextLabel ? ' · '.$contextLabel : '').' ('.$definition['unit'].')',
                        'method' => $definition['unit'] === 'timestamp' ? 'Latest recorded time each day' : ($kind === 'series' ? 'Daily sample average' : 'Daily value; average when multiple readings exist'),
                        'days' => [],
                    ];
                    $day = &$metrics[$id]['days'][$date];
                    $day ??= ['date' => $date, 'sum' => 0, 'count' => 0, 'min' => $value, 'max' => $value, 'latest' => $value, 'latest_at' => $at, 'post_id' => $postId];
                    $day['sum'] += $value;
                    $day['count']++;
                    $day['min'] = min($day['min'], $value);
                    $day['max'] = max($day['max'], $value);
                    if ($at >= $day['latest_at']) { $day['latest'] = $value; $day['latest_at'] = $at; }
                    unset($day);
                }
            }
        }
    }

    public static function validDate(mixed $date): bool
    {
        if (! is_string($date)) return false;
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }

    public static function chart(array $metric, string $from, string $to): array
    {
        $days = array_values(array_filter($metric['days'], fn ($day) => $day['date'] >= $from && $day['date'] <= $to));
        if (! $days) return ['days' => [], 'segments' => [], 'points' => []];
        $values = array_map(fn ($day) => self::plotValue($day['value'], $day['date'], $metric['unit']), $days);
        $min = min($values); $max = max($values);
        $padding = $max === $min ? max(1, abs($min) * .02) : ($max - $min) * .1;
        $low = $min - $padding; $high = $max + $padding;
        // UTC date ordinals keep calendar-day spacing equal across DST changes.
        $start = strtotime($from.' UTC'); $end = strtotime($to.' UTC');
        $points = []; $segments = []; $segment = []; $previous = null;
        foreach ($days as $i => $day) {
            $time = strtotime($day['date'].' UTC');
            if ($previous !== null && $time - $previous > 86400) { $segments[] = implode(' ', $segment); $segment = []; }
            $x = $end === $start ? 320 : 12 + 616 * ($time - $start) / ($end - $start);
            $y = 178 - 156 * ($values[$i] - $low) / ($high - $low);
            $point = ['x' => round($x, 2), 'y' => round($y, 2), 'day' => $day];
            $points[] = $point; $segment[] = $point['x'].','.$point['y']; $previous = $time;
        }
        $segments[] = implode(' ', $segment);
        return compact('days', 'points', 'segments', 'min', 'max');
    }

    private static function plotValue(float $value, string $date, string $unit): float
    {
        if ($unit !== 'timestamp') return $value;
        $local = (new \DateTimeImmutable('@'.(int) $value))->setTimezone(new \DateTimeZone('Europe/Bucharest'));
        // Clock hours relative to the measurement date, including previous-night times.
        return (strtotime($local->format('Y-m-d').' UTC') - strtotime($date.' UTC')) / 3600 + (int) $local->format('H') + (int) $local->format('i') / 60 + (int) $local->format('s') / 3600;
    }
}
