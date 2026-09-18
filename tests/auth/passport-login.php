<?php

/** Passport browser login redirects; no WordPress, database, or real credentials. */
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\View\ViewServiceProvider;
use Laravel\Passport\Exceptions\AuthenticationException as PassportAuthenticationException;

$app = new Application(dirname(__DIR__, 2));
$app->instance('config', new Repository(['app' => ['debug' => false], 'view' => ['paths' => []]]));
$app->register(FilesystemServiceProvider::class);
$app->register(ViewServiceProvider::class);
$session = new Store('oauth-login-test', new ArraySessionHandler(120));
$session->start();
$app->instance('session', $session);
$app->instance('session.store', $session);
$app['router']->get('login-web3', fn () => 'Login')->name('loginweb3');
$authorize = $app['router']->get('oauth/authorize', fn () => 'Consent')->name('passport.authorizations.authorize');
$app['router']->getRoutes()->refreshNameLookups();
$handler = new Handler($app);
$checks = 0;
$check = function (bool $ok, string $label) use (&$checks): void {
    if (! $ok) {
        throw new RuntimeException($label);
    }
    $checks++;
};

$url = 'https://pacurar.dev/oauth/authorize?'.http_build_query([
    'response_type' => 'code', 'client_id' => 'fixture-client',
    'redirect_uri' => 'https://client.invalid/callback', 'scope' => 'mcp:use',
    'code_challenge' => 'fixture-challenge', 'code_challenge_method' => 'S256',
    'state' => 'fixture-state', 'resource' => 'https://pacurar.dev/mcp',
]);
$request = Request::create($url, 'GET', server: ['HTTP_ACCEPT' => 'text/html']);
$request->setLaravelSession($session);
$request->setRouteResolver(fn () => $authorize);
$app->instance('request', $request);
$response = $handler->render($request, new PassportAuthenticationException(guards: ['wordpress']));
$check($response->getStatusCode() === 302, 'Passport guest browser is redirected instead of receiving an empty 401');
$check($response->headers->get('Location') === 'https://pacurar.dev/login-web3', 'Login remains on the authorization hostname');
$check($session->get('url.intended') === $request->fullUrl(), 'Full authorization URL including state, PKCE, resource and callback survives login');
$check(redirect()->intended('/')->getTargetUrl() === $request->fullUrl(), 'Login can resume the saved authorization request');

$request->headers->set('Accept', 'application/json');
$response = $handler->render($request, new PassportAuthenticationException(guards: ['wordpress']));
$check($response->getStatusCode() === 401 && $response->getData(true)['message'] === 'Unauthenticated.', 'JSON requests retain the authentication error');
$check(! $response->headers->has('Location') && ! $session->has('url.intended'), 'JSON failure neither redirects nor starts a browser login');

$request = Request::create('https://pacurar.dev/mcp', 'POST', server: ['HTTP_ACCEPT' => 'application/json']);
$app->instance('request', $request);
$response = $handler->render($request, new AuthenticationException(guards: ['api']));
$check($response->getStatusCode() === 401 && ! $response->headers->has('Location'), 'Unauthenticated MCP remains protected');

$request = Request::create('https://pacurar.dev/private-page', 'GET', server: ['HTTP_ACCEPT' => 'text/html']);
$app->instance('request', $request);
$response = $handler->render($request, new AuthenticationException(redirectTo: 'https://pacurar.dev/existing-login'));
$check($response->getStatusCode() === 302 && $response->headers->get('Location') === 'https://pacurar.dev/existing-login', 'Other authentication redirects retain their existing destination');

// Exceptions must become responses inside the HTTP pipeline so StartSession can save.
$app['config']->set('session', array_merge(require dirname(__DIR__, 2).'/config/session.php', [
    'driver' => 'array', 'lottery' => [0, 100], 'cookie' => 'oauth_fixture_session',
]));
$manager = new Illuminate\Session\SessionManager($app);
$app->instance('session', $manager);
$app->instance('session.store', $manager->driver());
$app->instance(Illuminate\Contracts\Debug\ExceptionHandler::class, $handler);
$app->forgetInstance('redirect');
$request = Request::create($url, 'GET', server: ['HTTP_ACCEPT' => 'text/html']);
$request->setRouteResolver(fn () => $authorize);
$app->instance('request', $request);
$response = (new Illuminate\Routing\Pipeline($app))->send($request)
    ->through([new Illuminate\Session\Middleware\StartSession($manager)])
    ->then(fn () => throw new PassportAuthenticationException(guards: ['wordpress']));
$check($response->getStatusCode() === 302 && count($response->headers->getCookies()) === 1, 'Login redirect issues the session cookie after Passport throws');
$persisted = unserialize($manager->driver()->getHandler()->read($request->session()->getId()));
$check(($persisted['url']['intended'] ?? null) === $request->fullUrl(), 'Return URL is persisted by session middleware, not just held in memory');

echo "$checks Passport login redirect checks passed\n";
