<?php

// Real SDK discovery routes, with no WordPress boot, credentials or database.
require dirname(__DIR__, 2).'/vendor/autoload.php';

function add_action(...$args) {}
function rest_get_url_prefix() { return 'wp-json'; }

$app = new Illuminate\Foundation\Application(dirname(__DIR__, 2));
$app->instance('config', new Illuminate\Config\Repository([
    'app' => ['url' => 'https://blog.test'],
    'view' => ['paths' => []],
    'database'=>['default'=>'sqlite','connections'=>['sqlite'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]],
    'auth'=>['guards'=>['api'=>['provider'=>'wordpress']]],
    'health_mcp' => require dirname(__DIR__, 2).'/config/health_mcp.php',
]));
Illuminate\Support\Facades\Facade::setFacadeApplication($app);
$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);
$app->register(Illuminate\View\ViewServiceProvider::class);
$app->register(Illuminate\Database\DatabaseServiceProvider::class);
$app->register(Illuminate\Translation\TranslationServiceProvider::class);
$app->register(Illuminate\Validation\ValidationServiceProvider::class);
$app->register(Laravel\Mcp\Server\McpServiceProvider::class);
$app->register(App\Providers\HealthMcpProvider::class);
$app->boot();
$router = $app['router'];
$router->get('oauth/authorize', fn () => 'Consent')->name('passport.authorizations.authorize');
$router->post('oauth/token', fn () => 'Token')->name('passport.token');
$router->getRoutes()->refreshNameLookups();
$checks = 0;
function check(bool $ok, string $message): void {
    global $checks;
    if (!$ok) throw new RuntimeException($message);
    $checks++;
}
foreach (['/.well-known/oauth-protected-resource', '/.well-known/oauth-protected-resource/mcp',
    '/.well-known/oauth-authorization-server', '/.well-known/oauth-authorization-server/mcp'] as $path) {
    $request = Illuminate\Http\Request::create('https://blog.test'.$path);
    $app->instance('request', $request);
    $response = $router->dispatch($request);
    $data = $response->getData(true);
    check($response->getStatusCode() === 200, 'Discovery route exists: '.$path);
    check($data['scopes_supported'] === ['mcp:use', 'health'], 'Both scopes advertised: '.$path);
    check(str_contains($response->headers->get('Cache-Control'), 'no-store'), 'Discovery is uncached: '.$path);
    if (str_contains($path, 'authorization-server')) {
        check($data['code_challenge_methods_supported'] === ['S256'], 'PKCE retained');
        check(str_ends_with($data['registration_endpoint'], '/oauth/register'), 'Registration endpoint retained');
    }
    $_SERVER['REQUEST_URI'] = $path;
    check(App\Health\AnalyticsNoCache::isRequest(), 'W3TC excludes discovery: '.$path);
    check(count(array_filter(App\Health\AnalyticsNoCache::rejectedUris([]),
        fn ($pattern) => preg_match('~'.$pattern.'~', $path))) > 0, 'W3TC page-cache reject rule matches: '.$path);
}
check(Laravel\Passport\Passport::hasScope('health') && Laravel\Passport\Passport::hasScope('mcp:use'), 'Passport accepts both scopes');
check(Laravel\Passport\Passport::$defaultScope === 'mcp:use health', 'Omitted scope defaults to both');
// Exercise the actual SDK registration controller against a disposable in-memory DB.
foreach (glob(dirname(__DIR__, 2).'/vendor/laravel/passport/database/migrations/*create_oauth_clients_table.php') as $migration) (require $migration)->up();
config(['mcp.redirect_domains'=>['client.example']]);
$request = Illuminate\Http\Request::create('https://blog.test/oauth/register', 'POST', [
    'client_name'=>'Scope test', 'redirect_uris'=>['https://client.example/callback'],
], server: ['HTTP_ACCEPT'=>'application/json']);
$app->instance('request', $request);
$response = $router->dispatch($request);
$data = $response->getData(true);
check($response->getStatusCode() === 201, 'SDK registration succeeds: '.$response->getContent());
check($data['scope'] === 'mcp:use health', 'Registration advertises space-separated scopes');
check($data['token_endpoint_auth_method'] === 'none' && $data['redirect_uris'] === ['https://client.example/callback'], 'SDK registration contract retained');
check(str_contains($response->headers->get('Cache-Control'), 'no-store'), 'Registration response is not cached');
$client = Laravel\Passport\Passport::client()->findOrFail($data['client_id']);
check($client->hasScope('health'), 'Registered client permits health tokens');
$request = Illuminate\Http\Request::create('https://blog.test/oauth/register', 'POST', ['redirect_uris'=>['https://untrusted.example/callback']], server: ['HTTP_ACCEPT'=>'application/json']);
$app->instance('request', $request);
$response = $router->dispatch($request);
check($response->getStatusCode() === 400 && !isset($response->getData(true)['scope']), 'Invalid registration remains rejected without scope metadata');
echo "$checks OAuth discovery checks passed\n";
