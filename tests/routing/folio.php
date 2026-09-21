<?php

// Exercise the theme provider with the real Laravel/Folio router, without WordPress or a database.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

$base = sys_get_temp_dir().'/blog-folio-routing-'.bin2hex(random_bytes(6));
mkdir($base.'/resources/views/pages', 0700, true);
mkdir($base.'/compiled', 0700, true);
mkdir($base.'/routes', 0700, true);
file_put_contents($base.'/routes/wp.php', '<?php // No additional WordPress fixture routes.');
file_put_contents($base.'/resources/views/pages/folio-fixture.blade.php', '<?php Laravel\Folio\name("folio.fixture"); ?>Folio fixture');
$app = new Illuminate\Foundation\Application($base);
$app->instance('config', new Illuminate\Config\Repository([
    'app'=>['url'=>'https://blog.test'],
    'view'=>['paths'=>[$base.'/resources/views'],'compiled'=>$base.'/compiled'],
]));
$app->instance('request', Request::create('https://blog.test/'));
Facade::setFacadeApplication($app);
foreach ([Illuminate\Filesystem\FilesystemServiceProvider::class, Illuminate\View\ViewServiceProvider::class,
    Laravel\Folio\FolioServiceProvider::class, LaraWelP\Foundation\Support\Providers\RouteServiceProvider::class] as $provider) $app->register($provider);
$router = $app['router'];
$router->middlewareGroup('web', []);
$app->instance('wpRouter', new class {
    public function dispatch(Request $request) { return response('WordPress fixture'); }
});
$app->register(App\Providers\FolioServiceProvider::class);
$app->boot();
$wordpressFallback = $router->getRoutes()->getByAction(LaraWelP\Foundation\Routing\WpRouteController::class.'@dispatch');
$checks = 0;
function check(bool $ok, string $message): void {
    global $checks;
    if (!$ok) throw new RuntimeException($message);
    $checks++;
}
try {
    check(route('folio.fixture') === 'https://blog.test/folio-fixture', 'Named Folio URL available before WordPress dispatch');
    $router->get('/named-redirect', fn () => redirect()->route('folio.fixture'));
    check($router->dispatch(Request::create('https://blog.test/named-redirect'))->headers->get('Location') === 'https://blog.test/folio-fixture', 'Laravel route redirects to named Folio page');
    foreach (['/', '/regular-page/', '/health/'] as $path) {
        $request = Request::create('https://blog.test'.$path);
        $app->instance('request', $request);
        check($router->getRoutes()->match($request) === $wordpressFallback, 'WordPress fallback retains priority: '.$path);
        check($router->dispatch($request)->getContent() === 'WordPress fixture', 'WordPress fallback executes: '.$path);
    }
    check(count(app(Laravel\Folio\FolioManager::class)->mountPaths()) === 1, 'Mount callbacks run once during application boot');
    $request = Request::create('https://blog.test/folio-fixture');
    $app->instance('request', $request);
    $request->setRouteResolver(fn () => $wordpressFallback);
    $response = app(Laravel\Folio\FolioManager::class)->handle($request);
    check($response->getStatusCode() === 200 && $response->getContent() === 'Folio fixture', 'Folio pages still render through their public handler');
    $missing = false;
    try {
        app(Laravel\Folio\FolioManager::class)->handle(Request::create('https://blog.test/missing-fixture'));
    } catch (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $error) {
        $missing = true;
    }
    check($missing, 'Unknown Folio page retains not-found behavior');
    echo "$checks Folio routing checks passed\n";
} finally {
    (new Illuminate\Filesystem\Filesystem)->deleteDirectory($base);
}
