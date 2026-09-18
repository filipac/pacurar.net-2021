<?php

namespace App\Health;

/** Export names and unit conversions; never selects a preferred provider. */
class AnalyticsCatalog
{
    public const VERSION = 1;
    public const PROVIDERS = ['apple_health' => 'apple', 'oura' => 'oura', 'withings' => 'withings'];
    public const GROUPS = ['body', 'activity', 'heart', 'sleep', 'recovery', 'vitals', 'mindfulness'];

    public static function fields(): array
    {
        static $fields;
        if ($fields !== null) return $fields;
        // Fields with genuinely different semantics retain different names, even
        // when their units match (e.g. walking-equivalent vs actual distance).
        $aliases = [
            'body.weight_kg' => ['withings.measure.1', 'apple_health.weight_body_mass'],
            'body.bmi' => ['apple_health.body_mass_index'],
            'body.body_fat_pct' => ['withings.measure.6', 'apple_health.body_fat_percentage'],
            'body.fat_mass_kg' => ['withings.measure.8'],
            'body.lean_mass_kg' => ['withings.measure.5', 'apple_health.lean_body_mass'],
            'body.muscle_mass_kg' => ['withings.measure.76'],
            'body.bone_mass_kg' => ['withings.measure.88'],
            'body.water_kg' => ['withings.measure.77'],
            'body.intracellular_water_kg' => ['withings.measure.169'],
            'body.extracellular_water_kg' => ['withings.measure.168'],
            'body.visceral_fat' => ['withings.measure.170'],
            'body.waist_cm' => ['apple_health.waist_circumference'],
            'activity.steps' => ['oura.daily_activity.steps', 'apple_health.step_count', 'withings.activity.steps'],
            'activity.distance_km' => ['withings.activity.distance'],
            'activity.walking_running_distance_km' => ['apple_health.walking_running_distance'],
            'activity.cycling_distance_km' => ['apple_health.cycling_distance'],
            'activity.equivalent_walking_distance_km' => ['oura.daily_activity.equivalent_walking_distance'],
            'activity.active_energy_kcal' => ['oura.daily_activity.active_calories', 'apple_health.active_energy', 'withings.activity.calories'],
            'activity.resting_energy_kcal' => ['apple_health.basal_energy_burned'],
            'activity.total_energy_kcal' => ['oura.daily_activity.total_calories', 'withings.activity.totalcalories'],
            'activity.exercise_minutes' => ['apple_health.apple_exercise_time'],
            'activity.floors' => ['apple_health.flights_climbed'],
            'heart.resting_hr_bpm' => ['apple_health.resting_heart_rate'],
            'heart.average_hr_bpm' => ['apple_health.heart_rate.Avg', 'oura.sleep.average_heart_rate', 'withings.activity.hr_average'],
            'heart.lowest_hr_bpm' => ['apple_health.heart_rate.Min', 'oura.sleep.lowest_heart_rate', 'withings.activity.hr_min'],
            'heart.max_hr_bpm' => ['apple_health.heart_rate.Max', 'withings.activity.hr_max'],
            'heart.sleep_average_hr_bpm' => ['withings.sleep.hr_average'],
            'heart.sleep_lowest_hr_bpm' => ['withings.sleep.hr_min'],
            'heart.sleep_max_hr_bpm' => ['withings.sleep.hr_max'],
            'heart.heart_rate_bpm' => ['withings.measure.11'],
            'heart.recording_heart_rate_bpm' => ['withings.heart.heart_rate'],
            'heart.hrv_ms' => ['apple_health.heart_rate_variability', 'oura.sleep.average_hrv'],
            'heart.vo2max' => ['apple_health.vo2_max', 'oura.vO2_max.vo2_max', 'withings.measure.123'],
            'heart.pwv_m_s' => ['withings.measure.91', 'oura.daily_cardiovascular_age.pulse_wave_velocity'],
            'heart.vascular_age_years' => ['withings.measure.155', 'oura.daily_cardiovascular_age.vascular_age'],
            'sleep.total_sleep_minutes' => ['apple_health.sleep_analysis.totalSleep', 'oura.sleep.total_sleep_duration', 'withings.sleep.total_sleep_time'],
            'sleep.asleep_minutes' => ['apple_health.sleep_analysis.asleep', 'withings.sleep.asleepduration'],
            'sleep.time_in_bed_minutes' => ['apple_health.sleep_analysis.inBed', 'oura.sleep.time_in_bed', 'withings.sleep.total_timeinbed'],
            'sleep.deep_sleep_minutes' => ['apple_health.sleep_analysis.deep', 'oura.sleep.deep_sleep_duration', 'withings.sleep.deepsleepduration'],
            'sleep.rem_sleep_minutes' => ['apple_health.sleep_analysis.rem', 'oura.sleep.rem_sleep_duration', 'withings.sleep.remsleepduration'],
            'sleep.light_sleep_minutes' => ['apple_health.sleep_analysis.core', 'oura.sleep.light_sleep_duration', 'withings.sleep.lightsleepduration'],
            'sleep.awake_minutes' => ['apple_health.sleep_analysis.awake', 'oura.sleep.awake_time', 'withings.sleep.wakeupduration'],
            'sleep.efficiency_pct' => ['oura.sleep.efficiency', 'withings.sleep.sleep_efficiency'],
            'sleep.sleep_score' => ['oura.daily_sleep.score', 'withings.sleep.sleep_score'],
            'sleep.bedtime' => ['apple_health.sleep_analysis.sleepStart'],
            'sleep.wake_time' => ['apple_health.sleep_analysis.sleepEnd'],
            'recovery.readiness_score' => ['oura.daily_readiness.score'],
            'recovery.temperature_deviation_c' => ['oura.daily_readiness.temperature_deviation'],
            'recovery.temperature_trend_deviation_c' => ['oura.daily_readiness.temperature_trend_deviation'],
            'recovery.respiratory_rate' => ['apple_health.respiratory_rate', 'oura.sleep.average_breath', 'withings.sleep.rr_average'],
            'mindfulness.mindful_minutes' => ['apple_health.mindful_minutes'],
        ];
        $names = [];
        foreach ($aliases as $path => $keys) foreach ($keys as $key) $names[$key] = explode('.', $path, 2);
        $workoutNames = [
            'duration' => 'duration_minutes', 'distance' => 'distance_km', 'manual_distance' => 'manual_distance_km',
            'activeEnergyBurned' => 'active_energy_kcal', 'calories' => 'active_energy_kcal', 'manual_calories' => 'manual_energy_kcal',
            'totalEnergy' => 'total_energy_kcal', 'avgHeartRate' => 'average_hr_bpm', 'hr_average' => 'average_hr_bpm',
            'maxHeartRate' => 'max_hr_bpm', 'hr_max' => 'max_hr_bpm', 'heartRate.min' => 'min_hr_bpm', 'hr_min' => 'min_hr_bpm',
        ];
        $fields = [];
        foreach (MetricCatalog::all() as $key => $definition) {
            if (! empty($definition['series'])) continue;
            $workout = $definition['endpoint'] === 'workout';
            $group = $workout ? 'workouts' : match ($definition['topic']) { 'weight', 'body-composition' => 'body', default => $definition['topic'] };
            $name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', substr($key, strpos($key, '.') + 1)));
            if ($workout) {
                $suffix = substr($key, strpos($key, '.workout.') + 9);
                $name = $workoutNames[$suffix] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $suffix));
            } elseif (isset($names[$key])) [$group, $name] = $names[$key];
            $unit = $definition['unit']; $factor = 1;
            if (str_ends_with($name, '_minutes') && in_array($unit, ['s', 'hr'], true)) { $factor = $unit === 's' ? 1 / 60 : 60; $unit = 'min'; }
            if (str_ends_with($name, '_km') && $unit === 'm') { $factor = .001; $unit = 'km'; }
            if ($name === 'efficiency_pct' && $unit === 'ratio') { $factor = 100; $unit = '%'; }
            if ($name === 'steps') $unit = 'steps';
            if ($name === 'floors') $unit = 'floors';
            if ($unit === 'timestamp') $unit = 'ISO 8601';
            $point = ! $workout && ($group === 'body' || in_array($definition['endpoint'], ['measure', 'heart', 'ecg', 'vo2_max', 'vO2_max', 'daily_cardiovascular_age'], true));
            // The source contract does not record the originating Apple device or
            // calculation method. Do not infer those from a metric's name.
            $provenance = 'unspecified';
            if ($definition['source'] !== 'apple_health' && ($unit === 'score' || $unit === 'kcal' || in_array($key, [
                'withings.measure.5', 'withings.measure.6', 'withings.measure.8', 'withings.measure.76', 'withings.measure.77',
                'withings.measure.88', 'withings.measure.155', 'withings.measure.168', 'withings.measure.169', 'withings.measure.170',
                'oura.daily_cardiovascular_age.vascular_age',
            ], true))) $provenance = 'provider_estimated';
            if ($key === 'withings.measure.1') $provenance = 'measured';
            $fields[$key] = ['group' => $group, 'field' => $name, 'unit' => $unit, 'factor' => $factor,
                'source_unit' => $definition['unit'], 'provider' => self::PROVIDERS[$definition['source']],
                'dataset' => $definition['endpoint'],
                'topic' => $definition['topic'], 'description' => $definition['label'],
                'shape' => $point ? 'point' : ($workout ? 'workout_metric' : 'daily'), 'provenance' => $provenance];
        }
        return $fields;
    }

    public static function schema(): array
    {
        $groups = array_fill_keys(array_merge(self::GROUPS, ['workouts']), []);
        foreach (self::fields() as $key => $definition) {
            $field = &$groups[$definition['group']][$definition['field']];
            $field ??= ['unit' => $definition['unit'], 'description' => $definition['description'], 'providers' => [], 'sources' => []];
            $field['providers'][] = $definition['provider'];
            $field['providers'] = array_values(array_unique($field['providers']));
            $field['sources'][$definition['provider']] = ['key' => $key, 'description' => $definition['description'],
                'shape' => $definition['shape'], 'dataset' => $definition['dataset'], 'provenance' => $definition['provenance'], 'source_unit' => $definition['source_unit'], 'conversion_factor' => $definition['factor']];
            unset($field);
        }
        return ['schema_version' => self::VERSION, 'timezone' => wp_timezone_string(), 'max_days' => TimelineApi::MAX_DAYS,
            'providers' => ['apple' => 'Apple Health', 'oura' => 'Oura', 'withings' => 'Withings'],
            'semantics' => [
                'dates' => 'Inclusive published measurement dates; empty days are included. days=N ends on today in the configured WordPress timezone.',
                'point' => 'One {value, measured_at} object, or an array of these objects when multiple observations exist. A missing source timestamp is null.',
                'daily' => 'One number (ISO 8601 string for clock times), or an array of {value, source_timestamp} when multiple observations exist. No averages, sums or provider selection are applied.',
                'timestamps' => 'Stored source timestamps converted to the configured WordPress timezone, never import times. Apple exports can supply daily bucket timestamps rather than exact measurement times. Sleep retains the published assigned date, including preceding-night timestamps.',
                'missing' => 'Absent metrics/providers are omitted; zero is only a stored zero. All top-level metric groups are objects.',
                'workouts' => 'Individual records, ordered by start time. provider identifies the exporting source; origin preserves the stored source of imported Apple workouts. Type/end can be null when the importer did not retain them. Partial Oura/Withings records are joined only on exact provider and start timestamp. The same real-world workout can appear under multiple providers; no cross-provider deduplication or totals are applied.',
                'provenance' => 'Describes the stored source, not clinical accuracy. unspecified means the importer does not retain enough origin metadata. Unit conversion does not change provenance.',
                'coverage' => 'Catalogued scalar metrics and individual workouts only. Raw sample series (including ECG waveforms), identifiers, notes, locations and arbitrary provider text are excluded. No missing metrics, BMI, cardiovascular-age deltas or workout end times are calculated.',
            ],
            'workout_record' => [
                'provider' => ['type' => 'string', 'values' => array_values(self::PROVIDERS)],
                'origin' => ['type' => 'string|null', 'values' => array_values(self::PROVIDERS)],
                'type' => ['type' => 'string|null', 'values' => MetricCatalog::WORKOUT_TYPES],
                'original_type' => ['type' => 'string', 'description' => 'Optional sanitized Apple Health activity name, at most 80 characters. Identifies activities such as Boxing when type is other. Omitted if unavailable.'],
                'start' => ['type' => 'string|null', 'unit' => 'ISO 8601'],
                'end' => ['type' => 'string|null', 'unit' => 'ISO 8601'],
                'repeated_metrics' => ['description' => 'Repeated workout fields are arrays of {value, source_timestamp}; they are never reduced.'],
            ], 'fields' => $groups];
    }
}
