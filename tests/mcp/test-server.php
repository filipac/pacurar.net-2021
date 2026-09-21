<?php

// Real MCP route and auth middleware; synthetic bearer guard, no credentials or database.
require dirname(__DIR__, 2).'/vendor/autoload.php';

function add_action($hook, $callback, ...$args) { $GLOBALS['testWordPressActions'][$hook][] = $callback; }
function rest_get_url_prefix() { return 'wp-json'; }

use App\Mcp\McpHttp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

$servers = require dirname(__DIR__, 2).'/config/mcp_servers.php';
$servers['/tools/mcp-fixture'] = ['server'=>App\Mcp\TestServer::class, 'config'=>'mcp_fixture', 'scopes'=>['mcp:use']];
$_SERVER['REQUEST_URI'] = '/tools/mcp-fixture?probe=1';
$app = new Illuminate\Foundation\Application(dirname(__DIR__, 2));
$app->instance('config', new Illuminate\Config\Repository([
    'app'=>['url'=>'https://blog.test', 'debug'=>false],
    'auth'=>['defaults'=>['guard'=>'api'], 'guards'=>['api'=>['driver'=>'fixture']]],
    'view'=>['paths'=>[]],
    'cache'=>['default'=>'array', 'stores'=>['array'=>['driver'=>'array']]],
    'logging'=>['default'=>'null', 'channels'=>['null'=>['driver'=>'monolog','handler'=>Monolog\Handler\NullHandler::class]]],
    'health_mcp'=>['enabled'=>false],
    'mcp_test'=>['enabled'=>true,'public_url'=>'https://blog.test/mcp-test','requests_per_minute'=>100],
    'mcp_servers'=>$servers,
    'mcp_fixture'=>['enabled'=>true,'public_url'=>'https://blog.test/tools/mcp-fixture','requests_per_minute'=>100],
    'mcp_oauth'=>require dirname(__DIR__, 2).'/config/mcp_oauth.php',
]));
Facade::setFacadeApplication($app);
$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, Illuminate\Foundation\Exceptions\Handler::class);
foreach ([Illuminate\Filesystem\FilesystemServiceProvider::class, Illuminate\View\ViewServiceProvider::class,
    Illuminate\Cache\CacheServiceProvider::class, Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class, Illuminate\Validation\ValidationServiceProvider::class,
    Laravel\Mcp\Server\McpServiceProvider::class, App\Providers\HealthMcpProvider::class] as $provider) $app->register($provider);
$app->boot();
$app['router']->aliasMiddleware('auth', App\Http\Middleware\Authenticate::class);
$user = new App\Models\WordpressUser(['user_login'=>'test-member','user_email'=>'member@example.test']);
$user->ID = 12;
$user->withAccessToken(new Laravel\Passport\AccessToken(['oauth_scopes'=>['mcp:use']]));
$app['auth']->extend('fixture', fn ($app) => new Illuminate\Auth\RequestGuard(
    fn (Request $request) => $request->bearerToken() === 'fixture-token' ? $user : null, $app['request']
));
$checks = 0;
function check(bool $ok, string $message): void {
    global $checks;
    if (!$ok) throw new RuntimeException($message);
    $checks++;
}
$send = function (string $method, array $params = [], bool $authenticated = true, string $path = '/mcp-test') use ($app) {
    $headers = ['CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json, text/event-stream','REMOTE_ADDR'=>'192.0.2.40'];
    if ($authenticated) $headers['HTTP_AUTHORIZATION'] = 'Bearer fixture-token';
    $request = Request::create('https://blog.test'.$path, 'POST', [], [], [], $headers,
        json_encode(['jsonrpc'=>'2.0','id'=>1,'method'=>$method,'params'=>(object)$params]));
    $app->instance('request', $request);
    $app['auth']->forgetGuards();
    return (new McpHttp)->handle($request, fn ($request) => $app['router']->dispatch($request));
};
$response = $send('tools/list', authenticated: false);
check($response->getStatusCode() === 401, 'Missing token is rejected');
$challenge = $response->headers->get('WWW-Authenticate');
check(str_contains($challenge, 'scope="mcp:use"') && !str_contains($challenge, 'health'), 'Test challenge does not request health');
check(str_contains($challenge, '/.well-known/oauth-protected-resource/mcp-test'), 'Test challenge references its own discovery document');
$response = $send('initialize', ['protocolVersion'=>'2025-11-25','capabilities'=>(object)[],'clientInfo'=>['name'=>'fixture','version'=>'1']]);
check($response->getStatusCode() === 200 && json_decode($response->getContent(), true)['result']['serverInfo']['name'] === 'pacurar.dev OAuth test', 'Independent test server initializes while health server is disabled');
$response = $send('tools/list');
$tools = json_decode($response->getContent(), true)['result']['tools'];
check(count($tools) === 1 && $tools[0]['name'] === 'wordpress_current_user', 'Only CurrentUser is exposed');
check($tools[0]['securitySchemes'][0]['scopes'] === ['mcp:use'], 'Identity tool requires no health scope');
$response = $send('tools/call', ['name'=>'wordpress_current_user','arguments'=>(object)[]]);
$data = json_decode($response->getContent(), true);
check(!$user->tokenCan('health') && $data['result']['structuredContent'] === ['email'=>'member@example.test','username'=>'test-member'], 'Minimal token can read only its own email and username');
check(str_contains($response->headers->get('Cache-Control'), 'no-store'), 'Identity response cannot be cached');
$response = $send('tools/call', ['name'=>'health_schema','arguments'=>(object)[]]);
check(isset(json_decode($response->getContent(), true)['error']), 'Health tools cannot be invoked on test server');
$response = $send('tools/call', ['name'=>'wordpress_current_user','arguments'=>(object)['user_id'=>1]]);
check(json_decode($response->getContent(), true)['result']['isError'] === true, 'Other user lookup is rejected');
$_SERVER['REQUEST_URI'] = '/mcp-test';
check(App\Mcp\McpServers::isRequest() && App\Mcp\McpServers::find('/mcp-test')['config'] === 'mcp_test', 'Transport routing selects test settings');
check(App\Health\AnalyticsNoCache::isRequest(), 'W3TC excludes the test server');
check(count(array_filter(App\Health\AnalyticsNoCache::rejectedUris([]), fn ($rule) => preg_match('~'.$rule.'~', '/mcp-test'))) > 0, 'W3TC page-cache rule excludes test endpoint');
// A third server exists only in configuration; the provider, bridge and transport are unchanged.
check(config('debugbar.enabled') === false, 'Registry request disables debug output during provider registration');
$response = $send('tools/list', path: '/tools/mcp-fixture');
check($response->getStatusCode() === 200 && count(json_decode($response->getContent(), true)['result']['tools']) === 1, 'Configuration alone registers a third authenticated MCP route');
$response = $send('tools/list', authenticated: false, path: '/tools/mcp-fixture');
check($response->getStatusCode() === 401 && str_contains($response->headers->get('WWW-Authenticate'), '/.well-known/oauth-protected-resource/tools/mcp-fixture'), 'Third server uses its own discovery challenge');
$request = Request::create('https://blog.test/.well-known/oauth-protected-resource/tools/mcp-fixture');
$app->instance('request', $request);
check($app['router']->dispatch($request)->getData(true)['scopes_supported'] === ['mcp:use'], 'Third server scopes come from its registry entry');
$_SERVER['REQUEST_URI'] = '/tools/mcp-fixture/?probe=1';
check(App\Mcp\McpServers::isRequest() && App\Health\AnalyticsNoCache::isRequest(), 'Third server inherits request recognition and cache exclusions');
check(count(array_filter(App\Health\AnalyticsNoCache::rejectedUris([]), fn ($rule) => preg_match('~'.$rule.'~', '/tools/mcp-fixture?probe=1'))) > 0, 'Third server gets a W3TC exclusion without code edits');
$_SERVER['REQUEST_URI'] = '/tools/mcp-fixture-not-a-server';
check(!App\Mcp\McpServers::isRequest(), 'Registry matching does not capture similar ordinary page paths');
$hooks = $GLOBALS['testWordPressActions']['template_redirect'];
check(count($hooks) === 1 && $hooks[0]() === null, 'WordPress bridge leaves non-MCP requests alone');
$unknown = $send('tools/list', path: '/not-an-mcp-server');
check($unknown->getStatusCode() === 404, 'Unregistered endpoints cannot enter MCP transport');
config(['mcp_fixture.requests_per_minute'=>1]);
check($send('tools/list', path: '/tools/mcp-fixture')->getStatusCode() === 429 && $send('tools/list')->getStatusCode() === 200, 'Server rate-limit buckets stay independent');
config(['mcp_fixture.requests_per_minute'=>100]);
if (in_array('--bridge', $argv, true)) {
    // Exercise response sending and exit in a subprocess, without any live WordPress/database.
    $_SERVER['REQUEST_URI'] = '/tools/mcp-fixture';
    $request = Request::create('https://blog.test/tools/mcp-fixture', 'POST', [], [], [],
        ['CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer fixture-token'],
        json_encode(['jsonrpc'=>'2.0','id'=>2,'method'=>'tools/list']));
    $app->instance('request', $request);
    $app['auth']->forgetGuards();
    $app->singleton(Illuminate\Contracts\Http\Kernel::class, Illuminate\Foundation\Http\Kernel::class);
    $hooks[0]();
    throw new RuntimeException('Bridge must stop WordPress after sending the MCP response');
}
$bridgeOutput = shell_exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).' --bridge');
$bridgeData = json_decode($bridgeOutput, true, flags: JSON_THROW_ON_ERROR);
check($bridgeData['id'] === 2 && count($bridgeData['result']['tools']) === 1, 'Real WordPress bridge callback dispatches configured third server and terminates cleanly');
config(['mcp_test.enabled'=>false]);
check($send('tools/list')->getStatusCode() === 404, 'Test endpoint can be disabled independently');
echo "$checks MCP test-server checks passed\n";
