<?php

// Real SDK discovery routes, with no WordPress boot, credentials or database.
require dirname(__DIR__, 2).'/vendor/autoload.php';

function add_action(...$args) {}
function rest_get_url_prefix() { return 'wp-json'; }

$app = new Illuminate\Foundation\Application(dirname(__DIR__, 2));
$app->instance('config', new Illuminate\Config\Repository([
    'app' => ['url' => 'https://blog.test'],
    'view' => ['paths' => []],
    'health_mcp' => require dirname(__DIR__, 2).'/config/health_mcp.php',
]));
Illuminate\Support\Facades\Facade::setFacadeApplication($app);
$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);
$app->register(Illuminate\View\ViewServiceProvider::class);
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
echo "$checks OAuth discovery checks passed\n";
