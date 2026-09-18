<?php
// Synthetic records in the integration harness's disposable database.
function overviewFixture(string $topic, string $date, string $key, float $value, string $status = 'publish'): int {
    $definition = App\Health\MetricCatalog::all()[$key];
    $id = wp_insert_post(['post_type' => 'health_entry', 'post_status' => $status, 'post_title' => 'Overview fixture']);
    update_post_meta($id, '_health_date', $date);
    update_post_meta($id, '_health_data', ['topic' => $topic, 'date' => $date, 'providers' => [
        $definition['source'] => ['metrics' => [['key' => $key, 'value' => $value]]],
    ]]);
    wp_set_object_terms($id, $topic, 'health_category');
    return $id;
}
$currentWeight = overviewFixture('weight', '2005-01-02', 'apple_health.weight_body_mass', 72);
overviewFixture('weight', '2005-01-01', 'withings.measure.1', 73);
foreach (['draft', 'private', 'trash'] as $status) overviewFixture('weight', '2005-01-03', 'withings.measure.1', 90, $status);
overviewFixture('sleep', '2005-01-01', 'oura.daily_sleep.score', 87);
overviewFixture('activity', '2005-01-03', 'oura.daily_activity.steps', 0);
$recovery = overviewFixture('recovery', '2005-01-02', 'oura.daily_readiness.score', 89);
// Newer recovery data without a readiness score must not masquerade as readiness.
overviewFixture('recovery', '2005-01-03', 'oura.daily_readiness.contributors.activity_balance', 99);
$overview = array_column(App\Health\Overview::latest(), null, 'topic');
check(count($overview) === 4, 'Overview shows four headline measurements');
check($overview['weight']['date'] === '2005-01-02' && $overview['weight']['metric']['value'] === 72.0, 'Overview prefers latest measurement date over later imports and hidden posts');
check($overview['weight']['metric']['source'] === 'apple_health', 'Newer provider reading beats older preferred provider');
check($overview['weight']['url'] === get_permalink($currentWeight), 'Overview links to the exact source entry');
check($overview['sleep']['date'] === '2005-01-01' && $overview['recovery']['date'] === '2005-01-02', 'Each overview measurement keeps its actual date');
check($overview['recovery']['metric']['value'] === 89.0, 'Readiness never substitutes an unrelated recovery score');
check($overview['activity']['metric']['value'] === 0.0, 'A published zero is a valid overview measurement');
$_GET = ['topic' => 'heart', 'source' => 'withings', 'paged' => 7];
check(array_column(App\Health\Overview::latest(), null, 'topic') === $overview, 'Archive filters and pagination do not change overview');
$_GET = [];
$entry = get_post_meta($currentWeight, '_health_data', true);
$entry['providers']['withings']['metrics'] = [['key' => 'withings.measure.1', 'value' => 71.5]];
update_post_meta($currentWeight, '_health_data', $entry);
$refreshed = array_column(App\Health\Overview::latest(), null, 'topic');
check($refreshed['weight']['metric']['value'] === 71.5 && $refreshed['weight']['metric']['source'] === 'withings', 'Overview uses archive provider priority within the same day and reflects updates');
wp_update_post(['ID' => $recovery, 'post_status' => 'draft']);
check(array_column(App\Health\Overview::latest(), null, 'topic')['recovery']['metric'] === null, 'Missing readiness is shown as unavailable rather than fabricated');

check($overview['weight']['change'] === null, 'Overview never compares a current provider with a different earlier provider');
overviewFixture('weight', '2004-12-30', 'apple_health.weight_body_mass', 74);
// Switch the latest reading back to Apple Health to exercise a gap in its data.
unset($entry['providers']['withings']);
update_post_meta($currentWeight, '_health_data', $entry);
overviewFixture('activity', '2005-01-01', 'oura.daily_activity.steps', 100);
overviewFixture('activity', '2005-01-02', 'oura.daily_activity.steps', 50, 'private');
$gaps = array_column(App\Health\Overview::latest(), null, 'topic');
check($gaps['weight']['change']['date'] === '2004-12-30' && $gaps['weight']['change']['text'] === '-2 kg', 'Overview uses the most recent earlier day with the same metric across date gaps');
check($gaps['activity']['change']['date'] === '2005-01-01' && $gaps['activity']['change']['text'] === '-100 steps', 'Overview excludes hidden baselines and preserves current zero values');
check($gaps['sleep']['change'] === null, 'Overview omits a change when no earlier value exists');
overviewFixture('activity', '2005-01-04', 'oura.daily_activity.steps', 0);
check(array_column(App\Health\Overview::latest(), null, 'topic')['activity']['change']['text'] === '0 steps', 'Overview shows zero change against a zero baseline');
for ($day = 3; $day <= 25; $day++) overviewFixture('weight', sprintf('2005-01-%02d', $day), 'apple_health.weight_body_mass', 80);
overviewFixture('weight', '2005-02-01', 'withings.measure.1', 72);
$batched = array_column(App\Health\Overview::latest(), null, 'topic')['weight'];
check($batched['change']['date'] === '2005-01-01' && $batched['change']['text'] === '-1 kg', 'Overview scans beyond the first summary batch without switching providers');
