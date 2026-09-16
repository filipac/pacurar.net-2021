<?php

/** Isolated middleware checks; no WordPress database, provider calls, or live redirects. */
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Http\Middleware\EnglishHealthPages;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

$language = $argv[1] ?? null;
if (! in_array($language, ['ro', 'en', 'without-wpml'], true)) {
    foreach (['ro', 'en', 'without-wpml'] as $language) {
        $process = proc_open([PHP_BINARY, __FILE__, $language], [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes);
        if (proc_close($process) !== 0) exit(1);
    }
    exit(0);
}
if ($language !== 'without-wpml') define('ICL_LANGUAGE_CODE', $language);
$app = new Container;
Container::setInstance($app);
$app->instance('config', new Repository(['health' => ['archive_slug' => 'health']]));
$middleware = new EnglishHealthPages;
$cases = [
    ['GET', '/health', '/health'],
    ['GET', '/health/?topic=heart&source=oura', '/health/?topic=heart&source=oura'],
    ['GET', '/health/page/2/?topic=sleep', '/health/page/2/?topic=sleep'],
    ['GET', '/health/weight-2026-09-16/', '/health/weight-2026-09-16/'],
    ['GET', '/health/compare?range=custom&from=2026-09-01&to=2026-09-16&metric=withings.measure.1%7Cdaily&lang=ro', '/health/compare?range=custom&from=2026-09-01&to=2026-09-16&metric=withings.measure.1%7Cdaily'],
    ['HEAD', '/health/compare', '/health/compare'],
    ['GET', '/health/compare?lang=ro', '/health/compare'],
    ['POST', '/health', null],
    ['GET', '/healthcare', null],
    ['GET', '/board-games', null],
    ['GET', '/wp-json/pacurar2020/v1/health-entries?topic=weight&date=2026-09-16', null],
    ['POST', '/wp-json/pacurar2020/v1/health-entries', null],
    ['GET', '/wp-admin/edit.php?post_type=health_entry', null],
    ['GET', '/?rest_route=%2Fwp%2Fv2%2Fhealth_entry', null],
];
foreach ($cases as [$method, $uri, $path]) {
    $request = Request::create('https://romanian.example'.$uri, $method);
    $response = $middleware->handle($request, fn () => new Response('next'));
    $shouldRedirect = $language === 'ro' && $path !== null;
    if ($response->getStatusCode() !== ($shouldRedirect ? 302 : 200)
        || ($shouldRedirect && $response->headers->get('Location') !== 'https://pacurar.dev'.$path)
        || (! $shouldRedirect && $response->getContent() !== 'next')) {
        throw new RuntimeException('Unexpected redirect for '.$language.' '.$method.' '.$uri);
    }
}
$stack = (new ReflectionClass(App\Http\Kernel::class))->getDefaultProperties()['middleware'];
if (! in_array(EnglishHealthPages::class, $stack, true)) throw new RuntimeException('Missing global middleware registration.');
echo count($cases).' English health page checks passed ('.$language.").\n";
