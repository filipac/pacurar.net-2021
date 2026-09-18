<?php

namespace App\Providers;

use App\Mcp\HealthApiClientInterface;
use App\Mcp\HealthMcpHttp;
use App\Mcp\HealthServer;
use App\Mcp\WordPressHealthApiClient;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\Facades\Mcp;

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
        // Outside the session/CSRF web group: all six tools only read public data.
        Mcp::web('/mcp', HealthServer::class);
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
