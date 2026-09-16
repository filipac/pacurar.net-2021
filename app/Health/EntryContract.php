<?php

namespace App\Health;

/** Version 1 wire contract. Keep this and metrics.json identical in both apps. */
class EntryContract
{
    public static function normalize(array $input): array
    {
        self::keys($input, ['schema_version', 'topic', 'date', 'timezone', 'providers', 'expected_revision']);
        if (($input['schema_version'] ?? null) !== 1 || ! isset(MetricCatalog::TOPICS[$input['topic'] ?? '']) || ($input['timezone'] ?? '') !== 'Europe/Bucharest') {
            self::invalid();
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $input['date'] ?? '');
        if (! $date || $date->format('Y-m-d') !== $input['date']) {
            self::invalid();
        }
        if (! is_array($input['providers'] ?? null) || ! $input['providers']) {
            self::invalid();
        }
        $out = array_intersect_key($input, array_flip(['schema_version', 'topic', 'date', 'timezone']));
        $out['providers'] = [];
        foreach ($input['providers'] as $source => $section) {
            if (! in_array($source, ['oura', 'withings'], true) || ! is_array($section)) {
                self::invalid();
            }
            self::keys($section, ['fetched_at', 'metrics', 'series']);
            self::timestamp($section['fetched_at'] ?? null);
            $clean = ['fetched_at' => $section['fetched_at'], 'metrics' => [], 'series' => []];
            foreach (['metrics', 'series'] as $kind) {
                if (! is_array($section[$kind] ?? null) || count($section[$kind]) > 10000) {
                    self::invalid();
                }
                foreach ($section[$kind] as $item) {
                    if (! is_array($item)) {
                        self::invalid();
                    }
                    self::keys($item, $kind === 'metrics' ? ['key', 'label', 'unit', 'value', 'at'] : ['key', 'label', 'unit', 'points']);
                    $def = MetricCatalog::all()[$item['key'] ?? ''] ?? null;
                    if (! $def || $def['source'] !== $source || $def['topic'] !== $input['topic'] || (! empty($def['series'])) !== ($kind === 'series')) {
                        self::invalid();
                    }
                    $row = ['key' => $item['key'], 'label' => $def['label'], 'unit' => $def['unit']];
                    if ($kind === 'metrics') {
                        self::number($item['value'] ?? null);
                        self::timestamp($item['at'] ?? null);
                        $row += ['value' => (float) $item['value'], 'at' => $item['at']];
                    } else {
                        if (! is_array($item['points'] ?? null) || ! $item['points'] || count($item['points']) > 20000) {
                            self::invalid();
                        }
                        $points = [];
                        foreach ($item['points'] as $point) {
                            if (! is_array($point)) {
                                self::invalid();
                            }
                            self::keys($point, ['at', 'value']);
                            self::timestamp($point['at'] ?? null);
                            self::number($point['value'] ?? null);
                            $points[$point['at']] = ['at' => $point['at'], 'value' => (float) $point['value']];
                        }
                        usort($points, fn ($a, $b) => strtotime($a['at']) <=> strtotime($b['at']));
                        $row['points'] = array_values($points);
                    }
                    $clean[$kind][] = $row;
                }
                usort($clean[$kind], fn ($a, $b) => strcmp($a['key'].($a['at'] ?? ''), $b['key'].($b['at'] ?? '')));
            }
            if (! $clean['metrics'] && ! $clean['series']) {
                self::invalid();
            }
            $out['providers'][$source] = $clean;
        }
        ksort($out['providers']);

        return $out;
    }

    public static function fingerprint(array $entry): string
    {
        $entry = self::normalize($entry);
        foreach ($entry['providers'] as &$section) {
            unset($section['fetched_at']);
        }

        return hash('sha256', json_encode($entry, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }

    /** Retain absent metric keys, replacing all observations of keys fetched again. */
    public static function merge(array $existing, array $incoming): array
    {
        foreach ($existing['providers'] ?? [] as $source => $previous) {
            if (! isset($incoming['providers'][$source])) {
                $incoming['providers'][$source] = $previous;

                continue;
            }
            foreach (['metrics', 'series'] as $kind) {
                $keys = array_column($incoming['providers'][$source][$kind], 'key');
                foreach ($previous[$kind] as $item) {
                    if (! in_array($item['key'], $keys, true)) {
                        $incoming['providers'][$source][$kind][] = $item;
                    }
                }
            }
        }

        return self::normalize($incoming);
    }

    private static function keys(array $value, array $allowed): void
    {
        if (array_diff(array_keys($value), $allowed)) {
            self::invalid();
        }
    }

    private static function timestamp(mixed $value): void
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/D', $value) || strtotime($value) === false) {
            self::invalid();
        }
    }

    private static function number(mixed $value): void
    {
        if ((! is_int($value) && ! is_float($value)) || ! is_finite((float) $value)) {
            self::invalid();
        }
    }

    private static function invalid(): never
    {
        throw new \InvalidArgumentException('Invalid health entry: only recognized numerical metrics and timestamps are accepted.');
    }
}
