<?php
// Runs only in the disposable integration database.
use App\Health\{Comparisons, DailySummary};

function comparisonFixture(array $entry, string $status = 'publish'): int {
    $id = wp_insert_post(['post_type' => 'health_entry', 'post_status' => $status, 'post_title' => 'Comparison fixture', 'post_name' => 'comparison-'.wp_generate_uuid4()]);
    update_post_meta($id, '_health_date', $entry['date']);
    update_post_meta($id, '_health_data', $entry);
    wp_set_object_terms($id, $entry['topic'], 'health_category');
    return $id;
}
function weightComparison(string $date, float $value): array {
    return ['topic' => 'weight', 'date' => $date, 'providers' => ['withings' => ['metrics' => [['key' => 'withings.measure.1', 'value' => $value]]]]];
}
$comparisonIds = [];
foreach (['2010-05-01' => 80, '2010-05-02' => 80, '2010-05-04' => 79, '2010-05-06' => 78, '2010-05-07' => 78, '2010-05-08' => 77] as $date => $value) {
    $comparisonIds[$date] = comparisonFixture(weightComparison($date, $value));
}
$currentEntry = weightComparison('2010-05-08', 77);
$summary = get_post_meta($comparisonIds['2010-05-08'], DailySummary::META, true);
check($summary['metrics']['withings.measure.1|daily']['value'] === 77.0, 'Publishing metadata automatically builds compact daily summary');
$beforeQueries = $wpdb->num_queries;
$history = new Comparisons([$currentEntry, weightComparison('2010-05-07', 78)]);
check($wpdb->num_queries - $beforeQueries === 1, 'Warm archive comparisons use one bounded summary query for multiple entries');
$beforeQueries = $wpdb->num_queries;
$comparison = $history->metric('weight', '2010-05-08', 'withings.measure.1|daily');
check($comparison['deltas'][0]['text'] === '-1 kg' && $comparison['deltas'][1]['text'] === '-3 kg', 'Exact previous-day and seven-days-earlier deltas calculated');
check($comparison['from'] === '2010-05-02' && count($comparison['chart']['days']) === 5, 'Weekly trend contains seven calendar days ending on entry date');
check(count($comparison['chart']['segments']) === 3, 'Weekly sparkline breaks at missing days');
check($comparison['chart']['days'][0]['date'] === '2010-05-02', 'Seven-day baseline is excluded from the seven-day sparkline');
for ($i = 0; $i < 20; $i++) $history->metric('weight', '2010-05-08', 'withings.measure.1|daily');
check($wpdb->num_queries === $beforeQueries, 'Rendering and reusing comparisons never issues per-metric queries');
$missing = (new Comparisons([weightComparison('2010-05-04', 79)]))->metric('weight', '2010-05-04', 'withings.measure.1|daily');
check($missing['deltas'] === [], 'Missing baseline days are never replaced by nearest available readings');
check($history->metric('weight', '2010-05-08', 'apple_health.weight_body_mass|daily') === null, 'Missing provider metric is never borrowed from another provider');

delete_post_meta($comparisonIds['2010-05-01'], DailySummary::META);
check((new Comparisons([$currentEntry]))->metric('weight', '2010-05-08', 'withings.measure.1|daily')['deltas'][1]['text'] === '-3 kg', 'Legacy entries are summarized when first needed');
check(get_post_meta($comparisonIds['2010-05-01'], DailySummary::META, true)['version'] === DailySummary::VERSION, 'Legacy daily summary persists for future requests');
$beforeQueries = $wpdb->num_queries;
new Comparisons([$currentEntry]);
check($wpdb->num_queries - $beforeQueries === 1, 'Backfilled entries need no raw payload reads on subsequent requests');
update_post_meta($comparisonIds['2010-05-01'], '_health_data', weightComparison('2010-05-01', 81));
check((new Comparisons([$currentEntry]))->metric('weight', '2010-05-08', 'withings.measure.1|daily')['deltas'][1]['text'] === '-4 kg', 'Historical corrections immediately refresh daily summary and deltas');
wp_update_post(['ID' => $comparisonIds['2010-05-07'], 'post_status' => 'private']);
$hidden = (new Comparisons([$currentEntry]))->metric('weight', '2010-05-08', 'withings.measure.1|daily');
check(count($hidden['deltas']) === 1 && $hidden['deltas'][0]['offset'] === 7, 'Unpublished/private measurements are excluded from comparisons');
delete_post_meta($comparisonIds['2010-05-06'], '_health_data');
check(! metadata_exists('post', $comparisonIds['2010-05-06'], DailySummary::META), 'Deleting health data deletes its derived daily summary');

$series = ['topic' => 'heart', 'date' => '2010-05-08', 'providers' => ['oura' => ['metrics' => [['key' => 'private.unknown', 'value' => 999]],
    'series' => [['key' => 'oura.heartrate.bpm', 'points' => [['value' => 50], ['value' => 70]]]]]]];
$priorSeries = $series; $priorSeries['date'] = '2010-05-07'; $priorSeries['providers']['oura']['series'][0]['points'] = [['value' => 50]];
comparisonFixture($priorSeries); comparisonFixture($series);
$seriesSummary = DailySummary::make($series);
check($seriesSummary['metrics']['oura.heartrate.bpm|daily']['value'] === 60.0 && ! str_contains(serialize($seriesSummary), 'private.unknown') && ! str_contains(serialize($seriesSummary), 'points'), 'Series summaries contain daily averages and exclude raw/unknown data');
$seriesDelta = (new Comparisons([$series]))->metric('heart', '2010-05-08', 'oura.heartrate.bpm|daily');
check($seriesDelta['deltas'][0]['text'] === '+10 bpm' && $seriesDelta['current']['count'] === 2, 'Series compare daily averages rather than individual samples');

$percent = ['topic' => 'body-composition', 'date' => '2010-05-07', 'providers' => ['withings' => ['metrics' => [['key' => 'withings.measure.6', 'value' => 0]]]]];
comparisonFixture($percent); $percent['date'] = '2010-05-08'; $percent['providers']['withings']['metrics'][0]['value'] = 2;
comparisonFixture($percent);
check((new Comparisons([$percent]))->metric('body-composition', '2010-05-08', 'withings.measure.6|daily')['deltas'][0]['text'] === '+2 pp', 'Percentage values use percentage-point changes and accept zero baseline');

$sleep = ['topic' => 'sleep', 'date' => '2010-03-27', 'providers' => ['apple_health' => ['metrics' => [
    ['key' => 'apple_health.sleep_analysis.sleepStart', 'value' => strtotime('2010-03-26T23:00:00+02:00')],
]]]];
comparisonFixture($sleep);
$sleep['date'] = '2010-03-28'; $sleep['providers']['apple_health']['metrics'][0]['value'] = strtotime('2010-03-27T23:30:00+02:00');
comparisonFixture($sleep);
$sleep['date'] = '2010-03-29'; $sleep['providers']['apple_health']['metrics'][0]['value'] = strtotime('2010-03-28T23:30:00+03:00');
comparisonFixture($sleep);
$clockChange = (new Comparisons([$sleep]))->metric('sleep', '2010-03-29', 'apple_health.sleep_analysis.sleepStart|daily');
check($clockChange['deltas'][0]['text'] === '0 min', 'Timestamp deltas compare local bedtime across DST without adding 24 hours');
check(count($clockChange['chart']['segments']) === 1, 'DST does not create a false gap in weekly trend');

$workout = ['topic' => 'activity', 'date' => '2010-05-08', 'providers' => ['apple_health' => ['workouts' => [
    ['type' => 'running', 'origin' => 'apple_health', 'metrics' => [['key' => 'apple_health.workout.distance', 'value' => 5]]],
    ['type' => 'running', 'origin' => 'oura', 'metrics' => [['key' => 'apple_health.workout.distance', 'value' => 10]]],
    ['type' => 'cycling', 'origin' => 'apple_health', 'metrics' => [['key' => 'apple_health.workout.distance', 'value' => 20]]],
]]]];
$workoutSummary = DailySummary::make($workout);
check(count($workoutSummary['metrics']) === 3 && $workoutSummary['metrics']['apple_health.workout.distance|running|apple_health']['value'] === 5.0, 'Workout summaries keep activity type and origin separate');

$laterId = comparisonFixture(weightComparison('2010-05-09', 76));
$unaffectedId = comparisonFixture(weightComparison('2010-05-10', 76));
$purges = (new App\Health\CacheInvalidator)->urls($comparisonIds['2010-05-02']);
check(in_array(get_permalink($laterId), $purges, true), 'Backfill purges same-topic single pages through seven days later');
check(! in_array(get_permalink($unaffectedId), $purges, true), 'Backfill leaves pages outside comparison dependency window cached');
