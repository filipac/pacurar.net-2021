<?php
// Runs only inside integration.php's disposable WordPress database.
wp_set_current_user($userId);
$bloodPressure = ['schema_version' => 1, 'topic' => 'heart', 'date' => '2001-01-09', 'timezone' => 'Europe/Bucharest', 'expected_revision' => null,
    'providers' => ['apple_health' => ['fetched_at' => '2001-01-09T10:00:00+02:00', 'series' => [], 'metrics' => [
        ['key' => 'apple_health.blood_pressure.systolic', 'value' => 116.85714285714, 'at' => '2001-01-09T00:00:00+02:00'],
        ['key' => 'apple_health.blood_pressure.diastolic', 'value' => 74.571428571429, 'at' => '2001-01-09T00:00:00+02:00'],
    ]]]];
$createdPressure = request('POST', $bloodPressure)->get_data();
check(($createdPressure['operation'] ?? null) === 'created', 'Apple blood pressure accepted by publishing contract');
check(wp_get_object_terms($createdPressure['id'], 'health_category', ['fields' => 'slugs']) === ['heart'], 'Blood pressure assigned to heart topic');
wp_set_current_user(0);
$publicPressure = rest_do_request(new WP_REST_Request('GET', '/wp/v2/health-entries/'.$createdPressure['id']))->get_data();
$pressureMetrics = array_column($publicPressure['health_data']['providers']['apple_health']['metrics'], null, 'key');
check(count($pressureMetrics) === 2, 'Public entry retains both blood pressure components');
foreach (['systolic' => 116.85714285714, 'diastolic' => 74.571428571429] as $component => $value) {
    $key = 'apple_health.blood_pressure.'.$component;
    $metric = $pressureMetrics[$key];
    check($metric['value'] === $value && $metric['unit'] === 'mmHg' && $metric['label'] === ucfirst($component).' blood pressure', $component.' exposes its public label, unit and decimal value');
    $trend = App\Health\Trends::published()[$key.'|daily'];
    check($trend['days']['2001-01-09']['value'] === $value && $trend['unit'] === 'mmHg', $component.' is available in comparison charts');
    $field = App\Health\AnalyticsCatalog::fields()[$key];
    check($field['group'] === 'heart' && $field['field'] === 'blood_pressure_'.$component && $field['unit'] === 'mmHg', $component.' is discoverable in the analytics catalog');
}
wp_set_current_user($userId);
check(request('POST', $bloodPressure)->get_data()['operation'] === 'unchanged', 'Identical blood pressure publication is unchanged');
$bloodPressure['expected_revision'] = $createdPressure['revision'];
$bloodPressure['providers']['apple_health']['metrics'][0]['value'] = 117.5;
$updatedPressure = request('POST', $bloodPressure)->get_data();
check(($updatedPressure['operation'] ?? null) === 'updated' && $updatedPressure['id'] === $createdPressure['id'], 'Corrected blood pressure updates the same heart entry');
