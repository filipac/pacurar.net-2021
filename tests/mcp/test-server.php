<?php

// Real MCP route and auth middleware; synthetic bearer guard, no credentials or database.
require dirname(__DIR__, 2).'/vendor/autoload.php';

function add_action(...$args) {}
function rest_get_url_prefix() { return 'wp-json'; }

use App\Mcp\TestMcpHttp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

$app = new Illuminate\Foundation\Application(dirname(__DIR__, 2));
$app->instance('config', new Illuminate\Config\Repository([
    'app'=>['url'=>'https://blog.test', 'debug'=>false],
    'auth'=>['defaults'=>['guard'=>'api'], 'guards'=>['api'=>['driver'=>'fixture']]],
    'view'=>['paths'=>[]],
    'cache'=>['default'=>'array', 'stores'=>['array'=>['driver'=>'array']]],
    'logging'=>['default'=>'null', 'channels'=>['null'=>['driver'=>'monolog','handler'=>Monolog\Handler\NullHandler::class]]],
    'health_mcp'=>['enabled'=>false],
    'mcp_test'=>['enabled'=>true,'public_url'=>'https://blog.test/mcp-test','requests_per_minute'=>100],
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
$send = function (string $method, array $params = [], bool $authenticated = true) use ($app) {
    $headers = ['CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json, text/event-stream','REMOTE_ADDR'=>'192.0.2.40'];
    if ($authenticated) $headers['HTTP_AUTHORIZATION'] = 'Bearer fixture-token';
    $request = Request::create('https://blog.test/mcp-test', 'POST', [], [], [], $headers,
        json_encode(['jsonrpc'=>'2.0','id'=>1,'method'=>$method,'params'=>(object)$params]));
    $app->instance('request', $request);
    $app['auth']->forgetGuards();
    return (new TestMcpHttp)->handle($request, fn ($request) => $app['router']->dispatch($request));
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
check(TestMcpHttp::isRequest() && !App\Mcp\HealthMcpHttp::isRequest(), 'Transport routing distinguishes the endpoints');
check(App\Health\AnalyticsNoCache::isRequest(), 'W3TC excludes the test server');
check(count(array_filter(App\Health\AnalyticsNoCache::rejectedUris([]), fn ($rule) => preg_match('~'.$rule.'~', '/mcp-test'))) > 0, 'W3TC page-cache rule excludes test endpoint');
config(['mcp_test.enabled'=>false]);
check($send('tools/list')->getStatusCode() === 404, 'Test endpoint can be disabled independently');
echo "$checks MCP test-server checks passed\n";
