<?php

namespace App\Providers;

use App\Mcp\HealthApiClientInterface;
use App\Mcp\McpHttp;
use App\Mcp\McpServers;
use App\Mcp\HealthOAuthMetadata;
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
        if (McpServers::isRequest()) {
            config(['debugbar.enabled' => false]);
        }
    }

    public function boot(): void
    {
        $this->configurePassport();
        $this->registerOAuthRoutes();
        $this->registerMcpServers();
        $this->registerWordPressBridge();
    }

    private function configurePassport(): void
    {
        Passport::authorizationView(function ($parameters) {
            return view('mcp.authorize', $parameters);
        });

        Passport::tokensCan(array_replace(Passport::$scopes, config('mcp_oauth.scopes')));
        Passport::defaultScopes(config('mcp_oauth.default_scopes'));
    }

    private function registerOAuthRoutes(): void
    {
        Mcp::oauthRoutes();
        foreach ($this->app['router']->getRoutes() as $route) {
            if (str_starts_with($route->getName() ?? '', 'mcp.oauth.')
                || $route->getControllerClass() === \Laravel\Mcp\Server\Http\Controllers\OAuthRegisterController::class) {
                $route->middleware(HealthOAuthMetadata::class);
            }
        }
    }

    private function registerMcpServers(): void
    {
        foreach (McpServers::all() as $path => $server) {
            Mcp::web($path, $server['server'])->middleware('auth:api');
        }
    }

    private function registerWordPressBridge(): void
    {
        add_action('template_redirect', function () {
            if (! McpServers::isRequest()) {
                return;
            }
            $request = request();
            $response = (new Pipeline($this->app))->send($request)
                ->through([\App\Http\Middleware\TrustProxies::class, McpHttp::class])
                ->then(fn ($request) => $this->app['router']->dispatch($request));
            $response->send();
            $this->app->make(\Illuminate\Contracts\Http\Kernel::class)->terminate($request, $response);
            exit;
        }, 0);
    }
}
