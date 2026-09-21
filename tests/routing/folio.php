<?php

// Exercise the theme provider with the real Laravel/Folio router, without WordPress or a database.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use LaraWelP\Foundation\Events\WhenFolioRegisters;

$base = sys_get_temp_dir().'/blog-folio-routing-'.bin2hex(random_bytes(6));
mkdir($base.'/resources/views/pages', 0700, true);
mkdir($base.'/compiled', 0700, true);
file_put_contents($base.'/resources/views/pages/folio-fixture.blade.php', 'Folio fixture');
$app = new Illuminate\Foundation\Application($base);
$app->instance('config', new Illuminate\Config\Repository([
    'app'=>['url'=>'https://blog.test'],
    'view'=>['paths'=>[$base.'/resources/views'],'compiled'=>$base.'/compiled'],
]));
Facade::setFacadeApplication($app);
foreach ([Illuminate\Filesystem\FilesystemServiceProvider::class, Illuminate\View\ViewServiceProvider::class,
    Laravel\Folio\FolioServiceProvider::class] as $provider) $app->register($provider);
$router = $app['router'];
$router->middlewareGroup('web', []);
// Same catch-all URI as LaraWelP: eager Folio mounting would overwrite this route.
$wordpressFallback = $router->any('{fallbackPlaceholder}', fn () => response('WordPress fixture'))
    ->where('fallbackPlaceholder', '.*')->fallback();
$app->register(App\Providers\FolioServiceProvider::class);
$app->boot();
$checks = 0;
function check(bool $ok, string $message): void {
    global $checks;
    if (!$ok) throw new RuntimeException($message);
    $checks++;
}
try {
    foreach (['/', '/regular-page/', '/health/'] as $path) {
        $request = Request::create('https://blog.test'.$path);
        $app->instance('request', $request);
        check($router->getRoutes()->match($request) === $wordpressFallback, 'WordPress fallback retains priority: '.$path);
        check($router->dispatch($request)->getContent() === 'WordPress fixture', 'WordPress fallback executes: '.$path);
    }
    // LaraWelP triggers this only after WordPress fails to resolve the request.
    $app['events']->dispatch(WhenFolioRegisters::EVENT_NAME);
    $request = Request::create('https://blog.test/folio-fixture');
    $app->instance('request', $request);
    $request->setRouteResolver(fn () => $wordpressFallback);
    $response = app(Laravel\Folio\FolioManager::class)->handle($request);
    check($response->getStatusCode() === 200 && $response->getContent() === 'Folio fixture', 'Deferred Folio pages still render');
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
