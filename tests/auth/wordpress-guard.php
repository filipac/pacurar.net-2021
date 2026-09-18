<?php

/** Stateful WordPress guard and Passport regressions; no live cookies or database. */
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Guard\WordpressGuard;
use Illuminate\Auth\GenericUser;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Cookie;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Exceptions\AuthenticationException;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Responses\SimpleViewResponse;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class WP_User
{
    public object $data;

    public string $user_login = 'fixture';

    public function __construct(public int $ID)
    {
        $this->data = (object) ['ID' => $ID, 'user_login' => 'fixture'];
    }
}
$events = [];
function wp_set_current_user($id)
{
    $GLOBALS['events'][] = ['current', $id];

    return new WP_User($id);
}
function wp_set_auth_cookie($id, $remember, $secure)
{
    $GLOBALS['events'][] = ['cookie', $id, $remember, $secure];
}
function is_ssl()
{
    return true;
}
function wp_logout()
{
    $GLOBALS['events'][] = ['logout'];
}
function do_action($event, ...$args)
{
    $GLOBALS['events'][] = [$event];
}

$app = new Container;
Container::setInstance($app);
$app->instance('config', new Repository);
Cookie::swap($cookies = new CookieJar);
$user = new GenericUser(['id' => 42]);
$provider = new class($user) implements UserProvider
{
    public function __construct(private Authenticatable $user) {}

    public function retrieveById($id)
    {
        return $id === 42 ? $this->user : null;
    }

    public function retrieveByToken($id, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new RuntimeException('Must not write Laravel remember tokens');
    }

    public function retrieveByCredentials(array $credentials)
    {
        return ($credentials['email'] ?? '') === 'fixture@example.invalid' ? $this->user : null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return ($credentials['password'] ?? '') === 'fixture-password';
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        throw new RuntimeException('WordPress owns password hashing');
    }
};
$request = Request::create('https://blog.test/oauth/authorize?prompt=consent', 'GET', [], ['blog_token' => 'fixture-token']);
$session = new Store('guard-test', new ArraySessionHandler(120));
$session->start();
$session->put('temp_token', 'fixture-token');
$request->setLaravelSession($session);
$guard = new WordpressGuard(fn () => $user, $request, $provider);
$checks = 0;
$check = function (bool $ok, string $label) use (&$checks): void {
    if (! $ok) {
        throw new RuntimeException($label);
    }
    $checks++;
};
$check($guard instanceof StatefulGuard, 'Guard satisfies Passport contract');
$check($guard->user() === $user, 'Existing wallet request callback is preserved');
$guard->logout();
$check($guard->guest() && $guard->user() === null, 'Logout cannot reauthenticate through the callback');
$check(! $request->cookies->has('blog_token') && ! $session->has('temp_token') && $cookies->hasQueued('blog_token'), 'Wallet cookie and temporary session token cleared');
$check(in_array(['logout'], $events, true), 'WordPress session logged out');
$credentials = ['email' => 'fixture@example.invalid', 'password' => 'fixture-password'];
$before = count($events);
$check($guard->validate($credentials) && $guard->guest() && count($events) === $before, 'Credential validation has no login side effects');
$check(! $guard->attempt([]) && ! $guard->attempt(['email' => 'fixture@example.invalid', 'password' => 'wrong']), 'Missing and invalid credentials rejected');
$check($guard->once($credentials) && $guard->id() === 42 && count($events) === $before, 'Once authenticates without persistent cookies');
$guard->logout();
$before = count($events);
$check($guard->onceUsingId(42) === $user && count($events) === $before, 'Once by ID is request-only');
$check($guard->onceUsingId(999) === false && $guard->loginUsingId(999) === false, 'Unknown user IDs rejected');
$sessionId = $session->getId();
$check($guard->attempt($credentials, true), 'Valid credentials establish persistent WordPress login');
$check(in_array(['cookie', 42, true, true], $events, true) && $sessionId !== $session->getId(), 'Remember flag uses WordPress cookies and Laravel session ID rotates');
$check($guard->loginUsingId(42) === $user, 'Login by ID returns authenticated user');
$before = count($events);
$check($guard->loginUsingWordpressUser(new WP_User(42)) && $guard->id() === 42 && count($events) === $before, 'Existing WordPress login bridge does not reissue cookies');
$check(! $guard->loginUsingWordpressUser(new WP_User(0)) && ! $guard->loginUsingWordpressUser(null), 'WordPress anonymous users are not authenticated');
$check($guard->viaRemember() === false, 'No Laravel remember-token authentication claimed');

// Exercise the actual Passport controller, including its forced-login logout path.
$server = new class extends AuthorizationServer
{
    public function __construct() {}

    public function validateAuthorizationRequest(ServerRequestInterface $request): AuthorizationRequestInterface
    {
        $auth = new AuthorizationRequest;
        $auth->setClient(new Laravel\Passport\Bridge\Client('fixture-client', 'Fixture', ['https://client.invalid/callback']));

        return $auth;
    }
};
$clients = new class extends ClientRepository
{
    public function find(string|int $id): ?Client
    {
        return new Client(['name' => 'Fixture']);
    }
};
$controller = new AuthorizationController($server, $guard, $clients);
$view = new SimpleViewResponse(fn ($params) => new Illuminate\Http\JsonResponse(['user_id' => $params['user']->getAuthIdentifier()]));
$psr = new GuzzleHttp\Psr7\ServerRequest('GET', 'https://blog.test/oauth/authorize');
$response = $controller->authorize($psr, $request, new GuzzleHttp\Psr7\Response, $view)->toResponse($request);
$check($response->getData(true)['user_id'] === 42 && $session->has('authToken') && $session->has('authRequest'), 'Passport consent uses the WordPress user and stores authorization state');
$request->query->set('prompt', 'login');
$prompted = false;
try {
    $controller->authorize($psr, $request, new GuzzleHttp\Psr7\Response, $view);
} catch (AuthenticationException $error) {
    $prompted = $error->guards() === ['wordpress'];
}
$check($prompted && $guard->guest() && $session->get('promptedForLogin') && ! $session->has('authRequest'), 'Passport forced login logs out and requests WordPress reauthentication');
echo "$checks WordPress guard / Passport checks passed\n";
