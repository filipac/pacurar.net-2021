<?php

namespace App\Mcp;

use App\Health\AnalyticsCatalog;
use App\Health\MetricCatalog;

/** JSON Schema for the canonical data, without repeating the full metric catalog. */
final class HealthOutputSchema
{
    public static function for(string $operation): array
    {
        $string = ['type' => 'string'];
        $number = ['type' => 'number'];
        $date = $string + ['format' => 'date'];
        $timestamp = ['type' => ['string', 'null'], 'format' => 'date-time'];
        $provider = $string + ['enum' => array_values(AnalyticsCatalog::PROVIDERS)];
        $value = ['anyOf' => [$number, $string + ['format' => 'date-time']],
            'description' => 'Numeric value in the reported unit, or an ISO 8601 timestamp for clock-time metrics.'];
        $observation = self::object(['date' => $date, 'value' => $value,
            'measured_at' => $timestamp, 'source_timestamp' => $timestamp], ['date', 'value']);
        $repeated = self::list(self::object(['value' => $value, 'source_timestamp' => $timestamp]));
        $reading = ['anyOf' => [$value, self::object(['value' => $value, 'measured_at' => $timestamp]),
            self::list(['anyOf' => [self::object(['value' => $value, 'measured_at' => $timestamp]),
                self::object(['value' => $value, 'source_timestamp' => $timestamp])]])],
            'description' => 'A daily scalar, a timestamped point, or all repeated observations. Missing providers are omitted; zero is a real reading.'];
        $meta = self::object([
            'schema_version' => ['type' => 'integer'], 'from' => $date, 'to' => $date,
            'timezone' => $string, 'days' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 365],
            'schema_url' => $string + ['format' => 'uri'],
        ]);
        $identity = ['metric' => $string + ['description' => 'Canonical group.field path from health_schema.'],
            'provider' => $provider, 'unit' => $string];
        $workoutProperties = ['provider' => $provider,
            'origin' => ['type' => ['string', 'null'], 'enum' => [...array_values(AnalyticsCatalog::PROVIDERS), null]],
            'type' => ['type' => ['string', 'null'], 'enum' => [...array_keys(MetricCatalog::WORKOUT_TYPES), null]],
            'start' => $timestamp, 'end' => $timestamp];
        foreach (AnalyticsCatalog::fields() as $field) {
            if ($field['group'] === 'workouts') {
                $workoutProperties[$field['field']] = ['anyOf' => [$value, $repeated],
                    'description' => $field['description'].' Unit: '.$field['unit'].'.'];
            }
        }
        $workoutRequired = ['provider', 'origin', 'type', 'start', 'end'];
        $workout = self::object($workoutProperties, $workoutRequired);
        $day = ['date' => $date];
        foreach (AnalyticsCatalog::GROUPS as $group) {
            $day[$group] = self::map(['$ref' => '#/$defs/providerReadings'])
                + ['description' => 'Metric field names from health_schema.fields.'.$group.', each containing separate provider readings.'];
        }
        $day['workouts'] = self::list($workout);

        $properties = match ($operation) {
            'schema' => self::catalog(),
            'timeline' => ['meta' => $meta, 'days' => self::list(self::object($day, ['date']))],
            'metric' => ['meta' => $meta] + $identity + ['observations' => self::list($observation)],
            'latest' => ['meta' => self::object((array) $meta['properties'] + [
                'lookback_days' => ['type' => 'integer', 'const' => 365], 'selection' => $string,
            ]), 'results' => self::list(self::object($identity + [
                'date' => ['type' => ['string', 'null'], 'format' => 'date', 'description' => 'Latest available assigned date, or null when no data exists in the lookback window.'],
                'observations' => self::list($observation),
            ]))],
            'workouts' => ['meta' => $meta, 'workouts' => self::list(self::object(
                ['date' => $date] + $workoutProperties, ['date', ...$workoutRequired]
            ))],
            'summary' => ['meta' => $meta] + $identity + ['statistics' => self::object([
                'count' => ['type' => 'integer', 'minimum' => 0, 'description' => 'Number of observations, not days.'],
                'missing_days' => self::list($date),
                ...array_fill_keys(['min', 'max', 'mean', 'median', 'numeric_change'], ['type' => ['number', 'null']]),
                'first' => ['anyOf' => [$observation, ['type' => 'null']]],
                'last' => ['anyOf' => [$observation, ['type' => 'null']]],
            ])],
        };

        // Keep top-level properties visible to clients while covering isError results too.
        // Laravel's schema builder does not support typed maps or root alternatives.
        $result = self::object($properties + ['error' => self::object(['code' => $string, 'message' => $string])], []) + [
            'oneOf' => [['required' => array_keys($properties)], ['required' => ['error']]],
        ];
        if ($operation === 'timeline') {
            $result['$defs'] = ['reading' => $reading, 'providerReadings' => self::object(
                array_fill_keys(array_values(AnalyticsCatalog::PROVIDERS), ['$ref' => '#/$defs/reading']), []
            )];
        }

        return $result;
    }

    private static function catalog(): array
    {
        $string = ['type' => 'string'];
        $source = self::object([
            'key' => $string, 'description' => $string, 'shape' => $string,
            'dataset' => $string, 'provenance' => $string, 'source_unit' => $string,
            'conversion_factor' => ['type' => 'number'],
        ]);
        $field = self::object(['unit' => $string, 'description' => $string,
            'providers' => self::list($string), 'sources' => self::map($source)]);
        $enumDescriptor = self::object(['type' => $string, 'values' => self::list($string)]);
        $timeDescriptor = self::object(['type' => $string, 'unit' => $string]);

        return [
            'schema_version' => ['type' => 'integer'], 'timezone' => $string,
            'max_days' => ['type' => 'integer'], 'providers' => self::map($string),
            'semantics' => self::map($string),
            'workout_record' => self::object([
                'provider' => $enumDescriptor, 'origin' => $enumDescriptor,
                'type' => self::object(['type' => $string, 'values' => self::map($string)]),
                'start' => $timeDescriptor, 'end' => $timeDescriptor,
                'repeated_metrics' => self::object(['description' => $string]),
            ]),
            'fields' => self::map(self::map($field)) + ['description' => 'Group → metric field → unit, description and per-provider provenance.'],
        ];
    }

    private static function object(array $properties, ?array $required = null): array
    {
        return ['type' => 'object', 'properties' => (object) $properties,
            'required' => $required ?? array_keys($properties), 'additionalProperties' => false];
    }

    private static function map(array $value): array
    {
        return ['type' => 'object', 'additionalProperties' => $value];
    }

    private static function list(array $items): array
    {
        return ['type' => 'array', 'items' => $items];
    }
}
