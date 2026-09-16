<?php
/** Standalone, isolated WordPress integration suite. No PHPUnit dependency.
 * php tests/health/integration.php
 * Creates and drops only a new health_journal_test_* database; never modifies blog tables.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
register_shutdown_function(function () { $e = error_get_last(); if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR])) fwrite(STDERR, $e['message']."\n".$e['file'].':'.$e['line']."\n"); });
$healthThemeRoot = dirname(__DIR__, 2);
$wpRoot = dirname($healthThemeRoot, 3).'/';
if (($argv[1] ?? '') !== 'child') {
    define('SHORTINIT', true);
    require $wpRoot.'wp-load.php';
    $database = 'health_journal_test_'.bin2hex(random_bytes(6));
    $directory = sys_get_temp_dir().'/'.$database;
    mkdir($directory, 0700);
    $configuration = ['database' => $database, 'user' => DB_USER, 'password' => DB_PASSWORD, 'host' => DB_HOST, 'directory' => $directory];
    file_put_contents($directory.'/config.json', json_encode($configuration, JSON_THROW_ON_ERROR));
    chmod($directory.'/config.json', 0600);
    if ($wpdb->query("CREATE DATABASE `$database` CHARACTER SET utf8mb4") === false) throw new RuntimeException('Cannot create isolated health test database.');
    try {
        $process = proc_open([PHP_BINARY, __FILE__, 'child', $directory.'/config.json'], [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes);
        $exit = proc_close($process);
    } finally {
        $wpdb->query("DROP DATABASE `$database`");
        unlink($directory.'/config.json');
        foreach (glob($directory.'/*') as $path) if (is_file($path)) unlink($path);
        @rmdir($directory);
    }
    exit($exit);
}
$config = json_decode(file_get_contents($argv[2]), true, flags: JSON_THROW_ON_ERROR);
if (! preg_match('/^health_journal_test_[a-f0-9]{12}$/D', $config['database'])) throw new RuntimeException('Unsafe database name.');
define('DB_NAME', $config['database']); define('DB_USER', $config['user']); define('DB_PASSWORD', $config['password']); define('DB_HOST', $config['host']);
define('DB_CHARSET', 'utf8mb4'); define('DB_COLLATE', ''); define('ABSPATH', $wpRoot);
define('WP_INSTALLING', true); define('WP_DEBUG', false); define('WP_CACHE', false); define('DISABLE_WP_CRON', true);
define('WP_CONTENT_DIR', $config['directory']); define('WP_CONTENT_URL', 'https://health-fixture.invalid/content');
define('WP_PLUGIN_DIR', $config['directory'].'/plugins'); define('WPMU_PLUGIN_DIR', $config['directory'].'/mu-plugins');
$_SERVER['HTTP_HOST'] = 'health-fixture.invalid'; $_SERVER['REQUEST_URI'] = '/'; $_SERVER['HTTPS'] = 'on';
$table_prefix = 'hjt_';
require $wpRoot.'wp-settings.php';
add_filter('pre_wp_mail', '__return_true');
require $wpRoot.'wp-admin/includes/upgrade.php';
wp_install('Health test', 'health_admin', 'fixture@example.invalid', false, '', 'fixture-password');
require $healthThemeRoot.'/vendor/autoload.php';
$app = new Illuminate\Container\Container;
Illuminate\Container\Container::setInstance($app);
$app->instance('config', new Illuminate\Config\Repository(['health' => require $healthThemeRoot.'/config/health.php']));
$provider = new App\Providers\HealthJournalProvider($app);
$provider->boot(); $provider->registerContent();
$server = rest_get_server();
$admin = get_user_by('login', 'health_admin');
$userId = wp_insert_user(['user_login' => 'fixture_publisher', 'user_pass' => 'fixture-password', 'role' => 'health_publisher']);
$subscriber = wp_insert_user(['user_login' => 'fixture_subscriber', 'user_pass' => 'fixture-password', 'role' => 'subscriber']);
$count = 0;
function check($condition, string $message): void { global $count; if (! $condition) throw new RuntimeException($message); $count++; echo "PASS $message\n"; }
function request(string $method, array $params) {
    $request = new WP_REST_Request($method, '/pacurar2020/v1/health-entries');
    if ($method === 'POST') { $request->set_header('content-type', 'application/json'); $request->set_body(json_encode($params)); }
    else $request->set_query_params($params);
    return rest_do_request($request);
}
$entry = ['schema_version' => 1, 'topic' => 'weight', 'date' => '2001-01-01', 'timezone' => 'Europe/Bucharest', 'expected_revision' => null, 'providers' => ['withings' => ['fetched_at' => '2001-01-01T10:00:00+02:00', 'metrics' => [['key' => 'withings.measure.1', 'label' => 'Weight', 'unit' => 'kg', 'value' => 80, 'at' => '2001-01-01T08:00:00+02:00']], 'series' => []]]];
wp_set_current_user(0); check(request('POST', $entry)->get_status() === 401, 'Anonymous write rejected');
wp_set_current_user($subscriber); check(request('POST', $entry)->get_status() === 403, 'Subscriber write rejected');
wp_set_current_user($userId);
check(! current_user_can('edit_posts') && current_user_can('publish_health_entries'), 'Publisher restricted to health entries');
$bad = $entry; $bad['providers']['withings']['device_id'] = 'private';
check(request('POST', $bad)->get_status() === 422, 'Private provider fields rejected');
$bad = $entry; $bad['providers']['withings']['metrics'][0]['key'] = 'withings.measure.999';
check(request('POST', $bad)->get_status() === 422, 'Unknown metrics rejected');
$created = request('POST', $entry);
check($created->get_status() === 200 && ($created->get_data()['operation'] ?? '') === 'created', 'Restricted publisher creates entry');
$id = $created->get_data()['id'];
check(request('POST', $entry)->get_data()['id'] === $id, 'Repeated create returns same post');
check(request('POST', $entry)->get_data()['operation'] === 'unchanged', 'Retry after lost response is unchanged');
check(wp_get_object_terms($id, 'health_category', ['fields' => 'slugs']) === ['weight'], 'Topic taxonomy assigned');
check(wp_get_object_terms($id, 'health_source', ['fields' => 'slugs']) === ['withings'], 'Source taxonomy assigned');
$read = request('GET', ['topic' => 'weight', 'date' => '2001-01-01'])->get_data();
$updated = $entry; $updated['providers']['withings']['metrics'][0]['value'] = 79;
check(request('POST', $updated)->get_status() === 409, 'Stale revision rejected');
$updated['expected_revision'] = $read['revision'];
check(request('POST', $updated)->get_data()['operation'] === 'updated', 'Current revision updates existing entry');
check(request('POST', $updated)->get_data()['operation'] === 'unchanged', 'Update retry is idempotent');
wp_set_current_user(0);
$public = rest_do_request(new WP_REST_Request('GET', '/wp/v2/health-entries/'.$id));
check($public->get_status() === 200 && $public->get_data()['health_data']['providers']['withings']['metrics'][0]['value'] === 79.0, 'Public REST exposes normalized measurements');
check(! str_contains(json_encode($public->get_data()), 'device_id'), 'Public REST contains no private provider field');
wp_set_current_user($userId);
$otherUser = wp_insert_user(['user_login' => 'other_publisher', 'user_pass' => 'fixture-password', 'role' => 'health_publisher']);
wp_set_current_user($otherUser);
check(request('POST', $updated)->get_status() === 403, 'Other publisher cannot overwrite author');
wp_set_current_user($userId);
$heart = $entry; $heart['topic'] = 'heart'; $heart['providers']['withings']['metrics'][0] = ['key' => 'withings.measure.11', 'value' => 70, 'at' => '2001-01-01T08:00:00+02:00'];
$heartResult = request('POST', $heart)->get_data();
$heart['expected_revision'] = $heartResult['revision'];
$heart['providers']['oura'] = ['fetched_at' => '2001-01-01T10:00:00+02:00', 'metrics' => [['key' => 'oura.sleep.average_heart_rate', 'value' => 60, 'at' => '2001-01-01T08:00:00+02:00']], 'series' => []];
check(request('POST', $heart)->get_data()['operation'] === 'updated', 'Two providers share one topic entry');
$heart['expected_revision'] = request('GET', ['topic' => 'heart', 'date' => '2001-01-01'])->get_data()['revision'];
unset($heart['providers']['withings']); $heart['providers']['oura']['metrics'][0]['value'] = 61;
check(request('POST', $heart)->get_status() === 409, 'Partial update cannot silently erase another provider');
$read = request('GET', ['topic' => 'weight', 'date' => '2001-01-01'])->get_data();
wp_update_post(['ID' => $id, 'post_title' => 'Manual edit']);
$updated['expected_revision'] = $read['revision']; $updated['providers']['withings']['metrics'][0]['value'] = 78;
check(request('POST', $updated)->get_status() === 409, 'Manual WordPress edit invalidates preview');

// Concurrent requests use independent database connections and PHP processes.
$fixtureFile = $config['directory'].'/concurrent.json';
file_put_contents($fixtureFile, json_encode($entry + ['unused' => false]));
$worker = $config['directory'].'/worker.php';
$workerCode = <<<'WORKER'
<?php
$config = json_decode(file_get_contents($argv[1]), true);
foreach (['DB_NAME' => $config['database'], 'DB_USER' => $config['user'], 'DB_PASSWORD' => $config['password'], 'DB_HOST' => $config['host'], 'DB_CHARSET' => 'utf8mb4', 'DB_COLLATE' => '', 'ABSPATH' => $argv[2], 'WP_CONTENT_DIR' => $config['directory'], 'WP_CONTENT_URL' => 'https://health-fixture.invalid/content', 'WP_PLUGIN_DIR' => $config['directory'].'/plugins', 'WPMU_PLUGIN_DIR' => $config['directory'].'/mu-plugins', 'WP_INSTALLING' => true, 'WP_DEBUG' => false, 'WP_CACHE' => false, 'DISABLE_WP_CRON' => true] as $key => $value) define($key, $value);
$table_prefix = 'hjt_'; $_SERVER['HTTP_HOST'] = 'health-fixture.invalid'; $_SERVER['REQUEST_URI'] = '/'; $_SERVER['HTTPS'] = 'on';
require ABSPATH.'wp-settings.php'; require $argv[3].'/vendor/autoload.php';
$app = new Illuminate\Container\Container; Illuminate\Container\Container::setInstance($app); $app->instance('config', new Illuminate\Config\Repository(['health' => require $argv[3].'/config/health.php']));
$provider = new App\Providers\HealthJournalProvider($app); $provider->boot(); $provider->registerContent();
wp_set_current_user((int) $argv[4]);
$entry = json_decode(file_get_contents($config['directory'].'/concurrent.json'), true); unset($entry['unused']); $entry['date'] = '2001-01-02';
$r = new WP_REST_Request('POST', '/pacurar2020/v1/health-entries'); $r->set_header('content-type', 'application/json'); $r->set_body(json_encode($entry));
$result = rest_do_request($r); echo json_encode(['status' => $result->get_status(), 'result' => $result->get_data()]);
WORKER;
file_put_contents($worker, $workerCode);
$processes = [];
for ($i = 0; $i < 3; $i++) {
    $p = proc_open([PHP_BINARY, $worker, $argv[2], $wpRoot, $healthThemeRoot, (string) $userId], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    fclose($pipes[0]); $processes[] = [$p, $pipes];
}
$ids = [];
foreach ($processes as [$p, $pipes]) { $out = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]); $exit = proc_close($p); $data = json_decode($out, true); check($exit === 0 && ($data['status'] ?? null) === 200, 'Concurrent request succeeds'); $ids[] = $data['result']['id']; }
check(count(array_unique($ids)) === 1, 'Concurrent creates produce exactly one post');
echo "\n$count integration checks passed; isolated database will be removed.\n";
