<?php

namespace App\Providers;

use App\Mcp\HealthApiClientInterface;
use App\Mcp\HealthMcpHttp;
use App\Mcp\HealthOAuthMetadata;
use App\Mcp\HealthServer;
use App\Mcp\WordPressHealthApiClient;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Passport\Passport;

final class HealthMcpProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HealthApiClientInterface::class, WordPressHealthApiClient::class);
        if (HealthMcpHttp::isRequest()) {
            config(['debugbar.enabled' => false]);
        }
    }

    public function boot(): void
    {
        // Stateless Passport authentication; tools read published health data or the token owner's identity.

        Passport::authorizationView(function ($parameters) {
            return view('mcp.authorize', $parameters);
        });

        Passport::tokensCan(array_replace(Passport::$scopes, config('health_mcp.scopes')));
        Passport::defaultScopes(array_keys(config('health_mcp.scopes')));

        Mcp::oauthRoutes();
        foreach ($this->app['router']->getRoutes() as $route) {
            if (str_starts_with($route->getName() ?? '', 'mcp.oauth.')
                || $route->getControllerClass() === \Laravel\Mcp\Server\Http\Controllers\OAuthRegisterController::class) {
                $route->middleware(HealthOAuthMetadata::class);
            }
        }
        Mcp::web('/mcp', HealthServer::class)->middleware('auth:api');
        add_action('template_redirect', function () {
            if (! HealthMcpHttp::isRequest()) {
                return;
            }
            $request = request();
            $response = (new Pipeline($this->app))->send($request)
                ->through([\App\Http\Middleware\TrustProxies::class, HealthMcpHttp::class])
                ->then(fn ($request) => $this->app['router']->dispatch($request));
            $response->send();
            $this->app->make(\Illuminate\Contracts\Http\Kernel::class)->terminate($request, $response);
            exit;
        }, 0);
    }
}
