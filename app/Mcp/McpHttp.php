<?php

namespace App\Mcp;

use App\Health\AnalyticsNoCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/** Dedicated stateless WordPress/Laravel transport boundary. */
final class McpHttp
{
    public function handle(Request $request, \Closure $next)
    {
        $server = McpServers::find($request->getPathInfo());
        try {
            $response = $server === null
                ? response()->json(['error' => 'not_found'], 404)
                : $this->dispatch($request, $next, $server);
        } catch (\Throwable $error) {
            report($error);
            $response = response()->json(['error' => 'service_unavailable'], 503);
        }
        foreach (AnalyticsNoCache::headers() as $key => $value) {
            $response->headers->set($key, $value);
        }
        if ($response->getStatusCode() === 401) {
            $response->headers->set('WWW-Authenticate', 'Bearer realm="mcp", resource_metadata="'
                .url('/.well-known/oauth-protected-resource'.$server['path']).'", scope="'
                .implode(' ', HealthOAuthMetadata::resourceScopes($server['path'])).'"');
        }

        return $response;
    }

    private function dispatch(Request $request, \Closure $next, array $server)
    {
        $settings = config($server['config'], []);
        if (! ($settings['enabled'] ?? false)) {
            return response()->json(['error' => 'not_found'], 404);
        }
        $public = parse_url($settings['public_url']);
        $expectedOrigin = ($public['scheme'] ?? 'https').'://'.($public['host'] ?? '').(isset($public['port']) ? ':'.$public['port'] : '');
        if ($request->getHost() !== ($public['host'] ?? '') || ($request->headers->has('Origin') && rtrim($request->header('Origin'), '/') !== $expectedOrigin)) {
            return response()->json(['error' => 'invalid_origin'], 403);
        }
        if (strlen($request->getContent()) > 16384) {
            return response()->json(['error' => 'request_too_large'], 413);
        }
        $key = ($server['rate_limit_prefix'] ?? 'mcp:'.$server['path'].':').hash('sha256', $request->ip() ?? 'unknown');
        if (RateLimiter::tooManyAttempts($key, max(1, (int) ($settings['requests_per_minute'] ?? 30)))) {
            return response()->json(['error' => 'rate_limited'], 429, ['Retry-After' => (string) RateLimiter::availableIn($key)]);
        }
        RateLimiter::hit($key, 60);
        return $next($request);
    }
}
