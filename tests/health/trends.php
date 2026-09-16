<?php
// Included by the isolated WordPress integration harness.
use App\Health\Trends;

function trendFixture(array $data, string $status = 'publish', string $type = 'health_entry'): int {
    $id = wp_insert_post(['post_type' => $type, 'post_status' => $status, 'post_title' => 'Synthetic trend fixture'] + ($status === 'future' ? ['post_date' => '2099-01-01 00:00:00'] : []));
    update_post_meta($id, '_health_data', $data);
    return $id;
}
$fixture = ['topic' => 'weight', 'date' => '2002-03-30', 'providers' => [
    'withings' => ['metrics' => [
        ['key' => 'withings.measure.1', 'value' => 80, 'at' => '2002-03-30T08:00:00+02:00'],
        ['key' => 'withings.measure.1', 'value' => 82, 'at' => '2002-03-30T09:00:00+02:00'],
        ['key' => 'private.unknown', 'value' => 999, 'label' => 'secret'],
    ]],
    'apple_health' => ['metrics' => [['key' => 'apple_health.weight_body_mass', 'value' => 79, 'at' => '2002-03-30T09:00:00+02:00']]],
]];
$publishedId = trendFixture($fixture);
foreach (['draft', 'private', 'trash', 'future'] as $status) { $private = $fixture; $private['date'] = '2002-04-01'; trendFixture($private, $status); }
trendFixture($private, 'publish', 'post');
$results = Trends::published();
$weight = $results['withings.measure.1|daily'];
$day = $weight['days']['2002-03-30'];
check($day['value'] === 81.0 && $day['min'] === 80.0 && $day['max'] === 82.0 && $day['count'] === 2, 'Trends retain daily mean, range and count');
check($results['apple_health.weight_body_mass|daily']['days']['2002-03-30']['value'] === 79.0, 'Trend providers remain separate');
check(! isset($weight['days']['2002-04-01']), 'Trends exclude drafts, private, trash, scheduled and ordinary posts');
check(! isset($results['private.unknown|daily']) && ! str_contains(json_encode($results), 'secret'), 'Trends only expose catalogued numerical data');
check($day['post_id'] === $publishedId, 'Trend values link back to original published entries');

trendFixture(['topic' => 'heart', 'date' => '2002-03-31', 'providers' => ['oura' => ['series' => [['key' => 'oura.heartrate.bpm', 'points' => [['value' => 0], ['value' => 60], ['value' => 90]]]]]]]);
$series = Trends::published()['oura.heartrate.bpm|daily'];
check($series['days']['2002-03-31']['value'] === 50.0 && $series['days']['2002-03-31']['count'] === 3, 'Sample trends average numerical values including zero');
check($series['method'] === 'Daily sample average', 'Sample aggregation is labelled explicitly');

trendFixture(['topic' => 'activity', 'date' => '2002-03-31', 'providers' => ['apple_health' => ['workouts' => [
    ['type' => 'running', 'origin' => 'apple_health', 'metrics' => [['key' => 'apple_health.workout.distance', 'value' => 5]]],
    ['type' => 'cycling', 'origin' => 'apple_health', 'metrics' => [['key' => 'apple_health.workout.distance', 'value' => 20]]],
]]]]);
$workouts = Trends::published();
check($workouts['apple_health.workout.distance|running|apple_health']['days']['2002-03-31']['value'] === 5.0 && $workouts['apple_health.workout.distance|cycling|apple_health']['days']['2002-03-31']['value'] === 20.0, 'Workout trends keep types and origins separate');

$chartMetric = ['unit' => 'kg', 'days' => []];
foreach (['2002-03-30', '2002-03-31', '2002-04-02'] as $date) $chartMetric['days'][$date] = ['date' => $date, 'value' => 80];
$chart = Trends::chart($chartMetric, '2002-03-30', '2002-04-02');
check(count($chart['segments']) === 2 && count($chart['points']) === 3, 'Chart leaves missing days as gaps and renders flat series');
check(abs(($chart['points'][1]['x'] - $chart['points'][0]['x']) * 2 - ($chart['points'][2]['x'] - $chart['points'][1]['x'])) < .02, 'Chart calendar spacing stays equal across DST');
check(count(Trends::chart($chartMetric, '2002-03-31', '2002-03-31')['days']) === 1, 'Custom date range includes both boundaries and single-day data');
check(Trends::chart($chartMetric, '2003-01-01', '2003-02-01')['days'] === [], 'Empty ranges do not fabricate values');
check(! Trends::validDate('2002-02-30') && ! Trends::validDate(['bad']) && Trends::validDate('2000-02-29'), 'Trend dates reject invalid days and non-string input');

$clock = ['unit' => 'timestamp', 'days' => [
    '2002-03-30' => ['date' => '2002-03-30', 'value' => strtotime('2002-03-29T23:00:00+02:00')],
    '2002-03-31' => ['date' => '2002-03-31', 'value' => strtotime('2002-03-30T23:00:00+02:00')],
]];
$clockChart = Trends::chart($clock, '2002-03-30', '2002-03-31');
check($clockChart['min'] === -1.0 && $clockChart['max'] === -1.0, 'Sleep timestamps chart local clock time relative to assigned day');
wp_update_post(['ID' => $publishedId, 'post_status' => 'draft']);
check(! isset(Trends::published()['withings.measure.1|daily']['days']['2002-03-30']), 'Unpublishing removes values from the comparison immediately');
