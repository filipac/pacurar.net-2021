<?php

namespace App\Mcp;

use App\Health\TimelineApi;

/** Filtering and descriptive statistics over the canonical, already-public representation. */
final class HealthTools
{
    public function __construct(private HealthApiClientInterface $client) {}

    public function execute(string $operation, array $input): array
    {
        $allowed = match ($operation) {
            'schema' => [],
            'timeline' => ['from', 'to', 'fields', 'providers', 'include_workouts'],
            'metric', 'summary' => ['from', 'to', 'metric', 'provider'],
            'latest' => ['metrics', 'providers'],
            'workouts' => ['from', 'to', 'type', 'provider', 'origin'],
            default => throw new HealthToolException('unknown_operation', 'Unsupported health operation.'),
        };
        if (array_diff(array_keys($input), $allowed)) {
            $this->fail('invalid_arguments', 'Unknown arguments. Only the documented tool arguments are supported.');
        }
        $schema = $this->client->schema();
        if ($operation === 'schema') {
            return $schema;
        }
        foreach (['provider', 'origin'] as $key) {
            if (array_key_exists($key, $input) && (! is_string($input[$key]) || ! isset($schema['providers'][$input[$key]]))) {
                $this->fail('unknown_provider', 'Use a provider identifier from health_schema.');
            }
        }
        if (isset($input['providers']) || array_key_exists('providers', $input)) {
            $this->list($input['providers'], 3, 'providers');
            foreach ($input['providers'] as $provider) {
                if (! isset($schema['providers'][$provider])) {
                    $this->fail('unknown_provider', 'Use provider identifiers from health_schema.');
                }
            }
        }
        if ($operation === 'latest') {
            $this->list($input['metrics'] ?? null, 50, 'metrics');
            foreach ($input['metrics'] as $metric) {
                $this->definition($schema, $metric);
            }
            $today = \Illuminate\Support\Facades\Date::now($schema['timezone'])->toDateTimeImmutable()->setTime(0, 0);
            $input['from'] = $today->modify('-364 days')->format('Y-m-d');
            $input['to'] = $today->format('Y-m-d');
        }
        if (! isset($input['from'],$input['to'])) {
            $this->fail('invalid_range', 'Supply both from and to as YYYY-MM-DD.');
        }
        try {
            TimelineApi::range(['from' => $input['from'], 'to' => $input['to']], new \DateTimeImmutable);
        } catch (\InvalidArgumentException $error) {
            $this->fail('invalid_range', $error->getMessage());
        }
        $fields = $input['fields'] ?? ($operation === 'latest' ? $input['metrics'] : null);
        if (array_key_exists('fields', $input)) {
            $this->list($input['fields'], 50, 'fields');
        }
        if ($fields !== null) {
            foreach ($fields as $field) {
                $this->definition($schema, $field);
            }
        }
        if (array_key_exists('include_workouts', $input) && ! is_bool($input['include_workouts'])) {
            $this->fail('invalid_arguments', 'include_workouts must be a boolean.');
        }
        if (in_array($operation, ['metric', 'summary'], true)) {
            $definition = $this->definition($schema, $input['metric'] ?? null);
            if (! isset($input['provider'])) {
                $this->fail('invalid_arguments', 'provider is required.');
            }
            if (! in_array($input['provider'], $definition['providers'], true)) {
                $this->fail('invalid_filters', 'This metric is not defined for the requested provider.');
            }
        }
        if (isset($input['providers']) && $fields !== null) {
            foreach ($fields as $field) {
                if (! array_intersect($input['providers'], $this->definition($schema, $field)['providers'])) {
                    $this->fail('invalid_filters', 'A requested metric is not defined for any selected provider.');
                }
            }
        }
        if (array_key_exists('type', $input) && (! is_string($input['type']) || ! array_key_exists($input['type'], $schema['workout_record']['type']['values']))) {
            $this->fail('invalid_filters', 'Use a workout type identifier from health_schema.');
        }
        // All validation precedes the potentially expensive canonical read.
        $timeline = $this->client->timeline($input['from'], $input['to']);
        if (! isset($timeline['meta'],$timeline['days']) || ! is_array($timeline['days'])) {
            $this->fail('upstream_unavailable', 'The public health API returned an invalid response.');
        }

        return match ($operation) {
            'timeline' => $this->filtered($timeline, $fields, $input['providers'] ?? null, $input['include_workouts'] ?? true),
            'metric' => ['meta' => $timeline['meta'], 'metric' => $input['metric'], 'provider' => $input['provider'], 'unit' => $definition['unit'], 'observations' => $this->observations($timeline, $input['metric'], $input['provider'])],
            'latest' => $this->latest($timeline, $schema, $input),
            'workouts' => $this->workouts($timeline, $input),
            'summary' => $this->summary($timeline, $input, $definition),
        };
    }

    private function filtered(array $timeline, ?array $fields, ?array $providers, bool $workouts): array
    {
        if ($fields === null && $providers === null && $workouts) {
            return $timeline;
        }
        foreach ($timeline['days'] as &$day) {
            foreach ($day as $group => $values) {
                if ($group === 'date') {
                    continue;
                }
                if ($group === 'workouts') {
                    if (! $workouts) {
                        unset($day[$group]);
                    } else {
                        $day[$group] = array_values(array_filter($values, fn ($record) => $providers === null || in_array($record['provider'], $providers, true)));
                    }

                    continue;
                }
                $kept = [];
                foreach ((array) $values as $field => $sources) {
                    if ($fields !== null && ! in_array($group.'.'.$field, $fields, true)) {
                        continue;
                    }
                    $sources = (array) $sources;
                    if ($providers !== null) {
                        $sources = array_intersect_key($sources, array_flip($providers));
                    }
                    if ($sources !== []) {
                        $kept[$field] = $sources;
                    }
                }
                if ($fields !== null && ! array_filter($fields, fn ($field) => str_starts_with($field, $group.'.'))) {
                    unset($day[$group]);
                } else {
                    $day[$group] = (object) $kept;
                }
            }
        }

        return $timeline;
    }

    private function observations(array $timeline, string $metric, string $provider): array
    {
        [$group,$field] = explode('.', $metric, 2);
        $observations = [];
        foreach ($timeline['days'] as $day) {
            $values = (array) ($day[$group] ?? []);
            $sources = (array) ($values[$field] ?? []);
            if (! array_key_exists($provider, $sources) || $sources[$provider] === null) {
                continue;
            }
            $value = $sources[$provider];
            $records = is_array($value) && array_is_list($value) ? $value : [$value];
            foreach ($records as $record) {
                $record = is_array($record) || is_object($record) ? (array) $record : ['value' => $record];
                if (array_key_exists('value', $record) && $record['value'] !== null) {
                    $observations[] = ['date' => $day['date']] + $record;
                }
            }
        }

        return $observations;
    }

    private function latest(array $timeline, array $schema, array $input): array
    {
        $results = [];
        foreach ($input['metrics'] as $metric) {
            $definition = $this->definition($schema, $metric);
            foreach ($input['providers'] ?? $definition['providers'] as $provider) {
                if (! in_array($provider, $definition['providers'], true)) {
                    continue;
                }
                $observations = $this->observations($timeline, $metric, $provider);
                $date = $observations ? max(array_column($observations, 'date')) : null;
                $results[] = ['metric' => $metric, 'provider' => $provider, 'unit' => $definition['unit'], 'date' => $date,
                    'observations' => array_values(array_filter($observations, fn ($record) => $record['date'] === $date))];
            }
        }

        return ['meta' => $timeline['meta'] + ['lookback_days' => 365, 'selection' => 'Latest available date per metric/provider; all observations on that date.'], 'results' => $results];
    }

    private function workouts(array $timeline, array $input): array
    {
        $records = [];
        foreach ($timeline['days'] as $day) {
            foreach ($day['workouts'] ?? [] as $record) {
                foreach (['provider', 'origin', 'type'] as $key) {
                    if (isset($input[$key]) && ($record[$key] ?? null) !== $input[$key]) {
                        continue 2;
                    }
                }
                $records[] = ['date' => $day['date']] + $record;
            }
        }

        return ['meta' => $timeline['meta'], 'workouts' => $records];
    }

    private function summary(array $timeline, array $input, array $definition): array
    {
        if ($definition['unit'] === 'ISO 8601') {
            $this->fail('non_numeric_metric', 'Descriptive statistics require a numerical metric. Use health_metric for clock times.');
        }
        $records = $this->observations($timeline, $input['metric'], $input['provider']);
        foreach ($records as $record) {
            if (! is_int($record['value']) && ! is_float($record['value'])) {
                $this->fail('non_numeric_metric', 'Descriptive statistics require numerical observations.');
            }
        }
        usort($records, fn ($a, $b) => ($a['date'] <=> $b['date']) ?: ((strtotime($a['measured_at'] ?? $a['source_timestamp'] ?? '') ?: 0) <=> (strtotime($b['measured_at'] ?? $b['source_timestamp'] ?? '') ?: 0)));
        $values = array_column($records, 'value');
        $count = count($values);
        $sorted = $values;
        sort($sorted, SORT_NUMERIC);
        $first = $records[0] ?? null;
        $last = $count ? $records[$count - 1] : null;
        $dates = array_unique(array_column($records, 'date'));

        return ['meta' => $timeline['meta'], 'metric' => $input['metric'], 'provider' => $input['provider'], 'unit' => $definition['unit'],
            'statistics' => ['count' => $count, 'missing_days' => array_values(array_diff(array_column($timeline['days'], 'date'), $dates)),
                'min' => $count ? min($values) : null, 'max' => $count ? max($values) : null, 'mean' => $count ? array_sum($values) / $count : null,
                'median' => $count ? ($sorted[intdiv($count - 1, 2)] + $sorted[intdiv($count, 2)]) / 2 : null,
                'first' => $first, 'last' => $last, 'numeric_change' => $count ? $last['value'] - $first['value'] : null]];
    }

    private function definition(array $schema, mixed $metric): array
    {
        if (! is_string($metric) || ! preg_match('/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/D', $metric)) {
            $this->fail('unknown_metric', 'Use an exact metric path from health_schema.');
        }
        [$group,$field] = explode('.', $metric, 2);
        if ($group === 'workouts' || ! isset($schema['fields'][$group][$field])) {
            $this->fail('unknown_metric', 'Unknown scalar metric. Use health_schema; use health_workouts for workout records.');
        }

        return $schema['fields'][$group][$field];
    }

    private function list(mixed $value, int $max, string $name): void
    {
        if (! is_array($value) || ! array_is_list($value) || count($value) < 1 || count($value) > $max) {
            $this->fail('invalid_arguments', "$name must be a non-empty list with at most $max items.");
        }
        foreach ($value as $item) {
            if (! is_string($item) || strlen($item) > 120) {
                $this->fail('invalid_arguments', "$name must contain strings of at most 120 characters.");
            }
        }
        if (count(array_unique($value)) !== count($value)) {
            $this->fail('invalid_arguments',"$name must contain unique items.");
        }
    }

    private function fail(string $code, string $message): never
    {
        throw new HealthToolException($code,$message);
    }
}
