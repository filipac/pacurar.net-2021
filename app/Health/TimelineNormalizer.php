<?php

namespace App\Health;

/** Lossless scalar normalization: no new health statistics or provider arbitration. */
class TimelineNormalizer
{
    private array $values = [];
    private array $workouts = [];
    private int $anonymousWorkout = 0;

    public function __construct(private \DateTimeZone $timezone) {}

    public function add(array $entry): void
    {
        $date = $entry['date'] ?? null;
        if (! Trends::validDate($date)) return;
        foreach ($entry['providers'] ?? [] as $source => $section) {
            if (! isset(AnalyticsCatalog::PROVIDERS[$source]) || ! is_array($section)) continue;
            foreach ($section['metrics'] ?? [] as $metric) {
                $definition = $this->definition($metric, $source, $entry['topic'] ?? '');
                if (! $definition) continue;
                $record = $this->observation($metric, $definition);
                if ($definition['group'] === 'workouts') {
                    // Native API workouts are stored as timestamped scalars,
                    // sometimes split across activity/heart/vitals entries.
                    $identity = $definition['provider'].'|'.($record['measured_at'] ?? 'unknown-'.++$this->anonymousWorkout);
                    $this->workouts[$date][$identity] ??= ['provider' => $definition['provider'], 'origin' => $definition['provider'],
                        'type' => null, 'start' => $record['measured_at'], 'end' => null, '_metrics' => []];
                    $this->workouts[$date][$identity]['_metrics'][$definition['field']][] = $record;
                } else {
                    $this->values[$date][$definition['group']][$definition['field']][$definition['provider']][] = $record + ['shape' => $definition['shape']];
                }
            }
            // Only Apple Health has structured workout records in the public
            // publishing contract. Copy named fields, never arbitrary text.
            if ($source !== 'apple_health' || ($entry['topic'] ?? '') !== 'activity') continue;
            foreach ($section['workouts'] ?? [] as $workout) {
                if (! is_array($workout) || ! isset(MetricCatalog::WORKOUT_TYPES[$workout['type'] ?? ''], AnalyticsCatalog::PROVIDERS[$workout['origin'] ?? ''])) continue;
                $start = $this->timestamp($workout['start'] ?? null);
                $end = $this->timestamp($workout['end'] ?? null);
                if (! $start || ! $end) continue;
                $identity = 'apple|'.$start.'|'.$workout['type'].'|'.$workout['origin'];
                $record = ['provider' => 'apple', 'origin' => AnalyticsCatalog::PROVIDERS[$workout['origin']], 'type' => $workout['type'],
                    'start' => $start, 'end' => $end, '_metrics' => []];
                if (($name = MetricCatalog::originalWorkoutType($workout['original_type'] ?? null)) !== null) {
                    $record['original_type'] = $name;
                }
                foreach ($workout['metrics'] ?? [] as $metric) {
                    $definition = $this->definition($metric, $source, 'activity');
                    if ($definition && $definition['group'] === 'workouts') $record['_metrics'][$definition['field']][] = $this->observation($metric, $definition);
                }
                $this->workouts[$date][$identity] = $record;
            }
        }
    }

    public function day(string $date): array
    {
        $day = ['date' => $date];
        foreach (AnalyticsCatalog::GROUPS as $group) {
            $fields = $this->values[$date][$group] ?? [];
            ksort($fields);
            foreach ($fields as &$providers) {
                ksort($providers);
                foreach ($providers as &$observations) $observations = $this->collapse($observations, ($observations[0]['shape'] ?? '') === 'point');
                unset($observations);
            }
            unset($providers);
            $day[$group] = (object) $fields;
        }
        $workouts = array_values($this->workouts[$date] ?? []);
        foreach ($workouts as &$workout) {
            foreach ($workout['_metrics'] as $field => $observations) $workout[$field] = $this->collapse($observations, false);
            unset($workout['_metrics']);
        }
        unset($workout);
        usort($workouts, fn ($a, $b) => self::compareTimes($a['start'], $b['start']) ?: strcmp($a['provider'].($a['type'] ?? ''), $b['provider'].($b['type'] ?? '')));
        $day['workouts'] = $workouts;
        return $day;
    }

    private function definition(mixed $metric, string $source, string $topic): ?array
    {
        if (! is_array($metric)) return null;
        $definition = AnalyticsCatalog::fields()[$metric['key'] ?? ''] ?? null;
        if (! $definition || $definition['provider'] !== AnalyticsCatalog::PROVIDERS[$source] || $definition['topic'] !== $topic
            || (! is_int($metric['value'] ?? null) && ! is_float($metric['value'] ?? null)) || ! is_finite((float) $metric['value'])) return null;
        return $definition;
    }

    private function observation(array $metric, array $definition): array
    {
        $value = $metric['value'] * $definition['factor'];
        if ($definition['source_unit'] === 'timestamp') $value = (new \DateTimeImmutable('@'.(int) $value))->setTimezone($this->timezone)->format(DATE_ATOM);
        return ['value' => $value, 'measured_at' => $this->timestamp($metric['at'] ?? null)];
    }

    private function collapse(array $observations, bool $point): mixed
    {
        usort($observations, fn ($a, $b) => self::compareTimes($a['measured_at'], $b['measured_at']));
        $values = array_map(fn ($item) => ['value' => $item['value'], $point ? 'measured_at' : 'source_timestamp' => $item['measured_at']], $observations);
        if (count($values) > 1) return $values;
        return $point ? $values[0] : $values[0]['value'];
    }

    private function timestamp(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/D', $value)) return null;
        try {
            $time = new \DateTimeImmutable($value);
            return $time->setTimezone($this->timezone)->format(str_contains($value, '.') ? 'Y-m-d\TH:i:s.uP' : DATE_ATOM);
        } catch (\Exception) { return null; }
    }

    private static function compareTimes(?string $a, ?string $b): int
    {
        if ($a === null || $b === null) return ($a !== null) <=> ($b !== null);
        return (new \DateTimeImmutable($a)) <=> (new \DateTimeImmutable($b));
    }
}
