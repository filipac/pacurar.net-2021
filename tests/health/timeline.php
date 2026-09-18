<?php
// Public analytics contract; all fixtures remain in the disposable database.
use App\Health\{AnalyticsCatalog, AnalyticsNoCache, TimelineApi};

function timelineRequest(array $params = [], string $method = 'GET') {
    $request = new WP_REST_Request($method, '/health/v1/timeline');
    $request->set_query_params($params);
    return rest_do_request($request);
}
function timelineFixture(string $date, string $topic, array $providers, string $status = 'publish', string $password = ''): int {
    $id = wp_insert_post(['post_type' => 'health_entry', 'post_status' => $status, 'post_password' => $password, 'post_title' => 'Private title never exported']
        + ($status === 'future' ? ['post_date' => '2099-01-01 12:00:00', 'post_date_gmt' => '2099-01-01 10:00:00'] : []));
    update_post_meta($id, '_health_date', $date);
    update_post_meta($id, '_health_data', ['date' => $date, 'topic' => $topic, 'timezone' => 'Europe/Bucharest', 'providers' => $providers]);
    return $id;
}
function timelineMetric(string $key, float $value, string $at = '2031-04-02T08:15:00+03:00'): array {
    return ['key' => $key, 'value' => $value, 'at' => $at];
}
update_option('timezone_string', 'Europe/Bucharest');
wp_set_current_user(0);
$weightLast = timelineFixture('2031-04-03', 'weight', ['withings' => ['metrics' => [timelineMetric('withings.measure.1', 71.77, '2031-04-03T05:15:00Z')]]]);
timelineFixture('2031-04-01', 'weight', ['withings' => ['metrics' => [timelineMetric('withings.measure.1', 72.2, '2031-04-01T08:15:00+03:00')]]]);
timelineFixture('2031-03-31', 'weight', ['withings' => ['metrics' => [timelineMetric('withings.measure.1', 99)]]]);
timelineFixture('2031-04-04', 'weight', ['withings' => ['metrics' => [timelineMetric('withings.measure.1', 99)]]]);
foreach (['draft', 'private', 'future', 'trash'] as $status) timelineFixture('2031-04-02', 'weight', ['withings' => ['metrics' => [timelineMetric('withings.measure.1', 999)]]], $status);
timelineFixture('2031-04-02', 'weight', ['withings' => ['metrics' => [timelineMetric('withings.measure.1', 998)]]], 'publish', 'secret');
timelineFixture('2031-04-02', 'activity', [
    'apple_health' => ['metrics' => [timelineMetric('apple_health.active_energy', 613), timelineMetric('apple_health.step_count', 0)],
        'account_id' => 'private-account', 'notes' => 'private-note', 'workouts' => [
            ['type' => 'cycling', 'origin' => 'apple_health', 'start' => '2031-04-02T18:02:00+03:00', 'end' => '2031-04-02T18:25:00+03:00',
                'route' => 'private-location', 'metrics' => [timelineMetric('apple_health.workout.duration', 1380), timelineMetric('apple_health.workout.distance', 7.3),
                    timelineMetric('apple_health.workout.activeEnergyBurned', 162), timelineMetric('apple_health.workout.avgHeartRate', 115)]],
            ['type' => 'strength', 'origin' => 'oura', 'start' => '2031-04-02T16:00:00+03:00', 'end' => '2031-04-02T16:30:00+03:00',
                'metrics' => [timelineMetric('apple_health.workout.duration', 1800)]],
        ]],
    'oura' => ['metrics' => [timelineMetric('oura.daily_activity.active_calories', 578), timelineMetric('oura.daily_activity.equivalent_walking_distance', 2100),
        timelineMetric('oura.workout.duration', 600, '2031-04-02T12:00:00+03:00'), timelineMetric('oura.workout.distance', 1400, '2031-04-02T12:00:00+03:00')]],
    'withings' => ['metrics' => [timelineMetric('withings.workout.calories', 150, '2031-04-02T11:00:00+03:00')]],
]);
timelineFixture('2031-04-02', 'heart', [
    'apple_health' => ['metrics' => [timelineMetric('apple_health.heart_rate_variability', 64), timelineMetric('apple_health.resting_heart_rate', 53)]],
    'oura' => ['metrics' => [timelineMetric('oura.sleep.average_hrv', 52)], 'series' => [['key' => 'oura.heartrate.bpm', 'points' => [['value' => 999]]]]],
    'withings' => ['metrics' => [timelineMetric('withings.workout.hr_average', 120, '2031-04-02T11:00:00+03:00')]],
]);
timelineFixture('2031-04-02', 'sleep', [
    'apple_health' => ['metrics' => [timelineMetric('apple_health.sleep_analysis.totalSleep', 7.5), timelineMetric('apple_health.sleep_analysis.sleepStart', strtotime('2031-04-01T23:10:00+03:00'))]],
    'oura' => ['metrics' => [timelineMetric('oura.sleep.deep_sleep_duration', 3600)]],
    'withings' => ['metrics' => [timelineMetric('withings.sleep.sleep_efficiency', .9)]],
]);
timelineFixture('2031-04-02', 'body-composition', ['withings' => ['metrics' => [timelineMetric('withings.measure.6', 9.24), timelineMetric('withings.measure.170', -1)]]]);
timelineFixture('2031-04-02', 'recovery', ['oura' => ['metrics' => [timelineMetric('oura.daily_readiness.score', 87), timelineMetric('oura.daily_stress.stress_high', 600)]]]);
timelineFixture('2031-04-02', 'vitals', ['oura' => ['metrics' => [timelineMetric('oura.daily_readiness.temperature_deviation', -.2)]]]);
timelineFixture('2031-04-02', 'mindfulness', ['apple_health' => ['metrics' => [timelineMetric('apple_health.mindful_minutes', 3)]]]);
$params = ['from' => '2031-04-01', 'to' => '2031-04-03'];
$response = timelineRequest($params);
check($response->get_status() === 200, 'Public timeline requires no credentials');
$data = json_decode(wp_json_encode($response->get_data()), true);
check($data['meta']['days'] === 3 && $data['meta']['timezone'] === 'Europe/Bucharest', 'Timeline metadata describes inclusive range and configured timezone');
check(array_column($data['days'], 'date') === ['2031-04-01', '2031-04-02', '2031-04-03'], 'Timeline merges topics into one chronological object per day');
check($data['days'][0]['body']['weight_kg']['withings']['value'] === 72.2 && $data['days'][2]['body']['weight_kg']['withings']['value'] === 71.77, 'Timeline includes both boundary dates and excludes adjacent dates');
$middle = $data['days'][1];
check(! isset($middle['body']['weight_kg']), 'Timeline excludes unpublished and password-protected readings even for otherwise public days');
check($middle['activity']['active_energy_kcal'] === ['apple' => 613, 'oura' => 578], 'Overlapping provider values remain separate without averaging or preferred provider');
check($middle['activity']['steps']['apple'] === 0 && ! isset($middle['activity']['steps']['oura']), 'Actual zero is preserved and missing provider metrics remain absent');
check(is_float($data['days'][2]['body']['weight_kg']['withings']['value']) && is_int($middle['activity']['active_energy_kcal']['apple']), 'JSON uses numerical values without unit strings');
check($data['days'][2]['body']['weight_kg']['withings']['measured_at'] === '2031-04-03T08:15:00+03:00', 'Weight preserves source time converted to the configured timezone');
check($middle['body']['visceral_fat']['withings']['value'] === -1, 'Questionable numerical measurements are not corrected or clamped');
check($middle['sleep']['total_sleep_minutes']['apple'] === 450 && $middle['sleep']['deep_sleep_minutes']['oura'] === 60 && $middle['sleep']['efficiency_pct']['withings'] === 90, 'Sleep hours/seconds and efficiency ratio receive explicit unit conversions');
check($middle['sleep']['bedtime']['apple'] === '2031-04-01T23:10:00+03:00', 'Sleep keeps its assigned day and preceding-night timestamp');
check($middle['activity']['equivalent_walking_distance_km']['oura'] === 2.1 && ! isset($middle['activity']['distance_km']['oura']), 'Walking-equivalent distance is not mislabeled as actual travelled distance');
check($middle['heart']['hrv_ms'] === ['apple' => 64, 'oura' => 52] && ! isset($middle['heart']['average_hr_bpm']), 'HRV providers remain distinct and raw samples do not create derived averages');
check($middle['recovery']['readiness_score']['oura'] === 87 && $middle['recovery']['temperature_deviation_c']['oura'] === -.2 && $middle['recovery']['daily_stress_stress_high']['oura'] === 600, 'Recovery exports both common fields and other stored catalogued metrics');
check(count($middle['workouts']) === 4 && array_column($middle['workouts'], 'start') === ['2031-04-02T11:00:00+03:00', '2031-04-02T12:00:00+03:00', '2031-04-02T16:00:00+03:00', '2031-04-02T18:02:00+03:00'], 'Individual workouts from all sources are sorted by start time');
$cycling = $middle['workouts'][3];
check($cycling['type'] === 'cycling' && $cycling['duration_minutes'] === 23 && $cycling['distance_km'] === 7.3 && $cycling['active_energy_kcal'] === 162 && $cycling['average_hr_bpm'] === 115, 'Structured workouts retain type, times and numerical session metrics');
check($middle['workouts'][2]['provider'] === 'apple' && $middle['workouts'][2]['origin'] === 'oura', 'Imported workout origin is retained separately from export provider');
check($middle['workouts'][0]['average_hr_bpm'] === 120 && $middle['workouts'][0]['active_energy_kcal'] === 150 && $middle['workouts'][0]['type'] === null && $middle['workouts'][0]['end'] === null, 'Partial native workouts join across topics without fabricating missing type or end');
check(! str_contains(wp_json_encode($data), 'private-') && ! str_contains(wp_json_encode($data), '999') && ! str_contains(wp_json_encode($data), 'fetched_at'), 'Timeline excludes private text, raw series, credentials and import timestamps');
$empty = timelineRequest(['from' => '2031-04-10', 'to' => '2031-04-11'])->get_data();
check(count($empty['days']) === 2 && is_object($empty['days'][0]['body']) && $empty['days'][0]['workouts'] === [], 'Empty calendar days contain empty objects and workout arrays, never fabricated zeroes');

// Repeated measurements and aggregates must not silently reduce to an average.
timelineFixture('2031-04-05', 'weight', ['withings' => ['metrics' => [timelineMetric('withings.measure.1', 71.61, '2031-04-05T08:15:00+03:00'), timelineMetric('withings.measure.1', 72.86, '2031-04-05T14:00:00+03:00')]]]);
timelineFixture('2031-04-05', 'sleep', ['oura' => ['metrics' => [timelineMetric('oura.sleep.total_sleep_duration', 3600), timelineMetric('oura.sleep.total_sleep_duration', 1800)]]]);
$repeated = json_decode(wp_json_encode(timelineRequest(['from' => '2031-04-05', 'to' => '2031-04-05'])->get_data()), true)['days'][0];
check(array_column($repeated['body']['weight_kg']['withings'], 'value') === [71.61, 72.86], 'Multiple point observations keep their values and source times');
check(array_column($repeated['sleep']['total_sleep_minutes']['oura'], 'value') === [60, 30], 'Repeated daily/session values are not summed or averaged');

$dst = new App\Health\TimelineNormalizer(new DateTimeZone('Europe/Bucharest'));
$dst->add(['date' => '2031-10-26', 'topic' => 'weight', 'providers' => ['withings' => ['metrics' => [
    timelineMetric('withings.measure.1', 72, '2031-10-26T03:15:00+02:00'),
    timelineMetric('withings.measure.1', 71, '2031-10-26T03:15:00+03:00'),
]]]]);
check(array_column($dst->day('2031-10-26')['body']->weight_kg['withings'], 'value') === [71.0, 72.0], 'Repeated local clock times during DST are ordered by their actual instants');
check(timelineRequest(['unrecognized' => 'value'])->get_status() === 400, 'Unsupported analytics query parameters are rejected');

foreach ([['from' => '2031-02-29', 'to' => '2031-03-01'], ['from' => '2031-04-03', 'to' => '2031-04-01'], ['from' => '2031-04-01'], ['to' => '2031-04-01'], ['from' => ['2031-04-01'], 'to' => '2031-04-02'], ['days' => '1.5'], ['days' => 0], ['days' => -1], ['days' => 366], ['days' => []], $params + ['days' => 30], ['from' => '2032-01-01', 'to' => '2032-12-31']] as $invalid) {
    check(timelineRequest($invalid)->get_status() === 400, 'Invalid or excessive timeline range rejected: '.json_encode($invalid));
}
check(timelineRequest(['days' => '365'])->get_data()['meta']['days'] === 365, 'Maximum 365-day range is accepted');
check(timelineRequest()->get_data()['meta']['days'] === 30, 'Timeline defaults to 30 recent calendar days');
check(TimelineApi::range(['days' => '2'], new DateTimeImmutable('2031-04-03T00:15:00+03:00')) === ['2031-04-02', '2031-04-03', 2], 'Recent range uses local day at midnight rather than UTC date');
check(TimelineApi::range(['from' => '2031-03-29', 'to' => '2031-03-31'], new DateTimeImmutable()) === ['2031-03-29', '2031-03-31', 3], 'Calendar range counts remain inclusive across a DST transition');
$healthQueries = [];
$querySpy = static function ($sql) use (&$healthQueries, $wpdb) { if (str_contains($sql, $wpdb->postmeta) || str_contains($sql, $wpdb->posts)) $healthQueries[] = $sql; return $sql; };
add_filter('query', $querySpy);
$queryRequest = new WP_REST_Request('GET', '/health/v1/timeline'); $queryRequest->set_query_params($params);
(new TimelineApi)->timeline($queryRequest);
remove_filter('query', $querySpy);
check(count($healthQueries) === 2, 'Nine published topic entries are read in two batches without per-post queries');
$previous = get_post_meta($weightLast, '_health_data', true);
$previous['providers']['withings']['metrics'][0]['value'] = 70;
update_post_meta($weightLast, '_health_data', $previous);
check(timelineRequest($params)->get_data()['days'][2]['body']->weight_kg['withings']['value'] == 70, 'Next request immediately sees updated data without stale export cache');
wp_update_post(['ID' => $weightLast, 'post_status' => 'private']);
check(! isset(timelineRequest($params)->get_data()['days'][2]['body']->weight_kg), 'Next request excludes data made private');
check(timelineRequest([], 'POST')->get_status() === 404, 'Public analytics endpoint has no write route');
$schemaResponse = rest_do_request(new WP_REST_Request('GET', '/health/v1/schema'));
$schema = $schemaResponse->get_data();
check($schemaResponse->get_status() === 200 && $schema['fields']['body']['weight_kg']['providers'] === ['withings', 'apple'], 'Public schema documents supported providers');
check($schema['fields']['sleep']['total_sleep_minutes']['sources']['apple']['conversion_factor'] === 60 && $schema['fields']['body']['weight_kg']['sources']['withings']['shape'] === 'point', 'Schema documents units, conversions and point-vs-daily shape');
check(count(AnalyticsCatalog::fields()) === count(array_filter(App\Health\MetricCatalog::all(), fn ($metric) => empty($metric['series']))), 'Every catalogued scalar metric is described by the analytics schema');
check(str_contains($response->get_headers()['Cache-Control'], 'no-store') && str_contains($schemaResponse->get_headers()['Cache-Control'], 'no-store'), 'Timeline and schema prohibit response caching');
check(defined('DONOTCACHEPAGE') && DONOTCACHEPAGE && defined('DONOTCACHEDB') && DONOTCACHEDB && defined('DONOTCACHEOBJECT') && DONOTCACHEOBJECT, 'Analytics requests explicitly bypass W3TC page, database and object caches');
$errorRequest = new WP_REST_Request('GET', '/health/v1/timeline'); $errorRequest->set_query_params(['days' => 0]);
$errorResponse = apply_filters('rest_post_dispatch', rest_do_request($errorRequest), rest_get_server(), $errorRequest);
check(str_contains($errorResponse->get_headers()['Cache-Control'], 'no-store'), 'Analytics validation errors also prohibit caching');
$originalUri = $_SERVER['REQUEST_URI'];
$_SERVER['REQUEST_URI'] = '/wp-json/health/v1/timeline?days=30';
check(apply_filters('w3tc_can_cache', true) === false, 'W3TC page-cache write filter rejects timeline requests');
$rejects = AnalyticsNoCache::rejectedUris(['/existing-exclusion']);
check($rejects[0] === '/existing-exclusion' && preg_match('~'.$rejects[1].'~i', $_SERVER['REQUEST_URI']) === 1 && preg_match('~'.$rejects[2].'~i', '/?rest_route=%2Fhealth%2Fv1%2Fschema') === 1, 'W3TC exclusions preserve existing rules and cover pretty and query-string REST URLs');
$_SERVER['REQUEST_URI'] = '/health/';
check(apply_filters('w3tc_can_cache', true) === true, 'Unrelated health pages keep their normal W3TC caching policy');
$_SERVER['REQUEST_URI'] = $originalUri;
