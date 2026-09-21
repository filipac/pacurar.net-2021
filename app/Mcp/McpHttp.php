<?php

namespace App\Mcp;

use App\Health\AnalyticsNoCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/** Dedicated stateless WordPress/Laravel transport boundary. */
abstract class McpHttp
{
    protected const PATH = '';
    protected const CONFIG = '';

    public static function isRequest(): bool
    {
        return rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/') === static::PATH;
    }

    public function handle(Request $request, \Closure $next)
    {
        try {
            $response = $this->dispatch($request, $next);
        } catch (\Throwable $error) {
            report($error);
            $response = response()->json(['error' => 'service_unavailable'], 503);
        }
        foreach (AnalyticsNoCache::headers() as $key => $value) {
            $response->headers->set($key, $value);
        }
        if ($response->getStatusCode() === 401) {
            $response->headers->set('WWW-Authenticate', 'Bearer realm="mcp", resource_metadata="'
                .url('/.well-known/oauth-protected-resource'.static::PATH).'", scope="'
                .implode(' ', HealthOAuthMetadata::resourceScopes(static::PATH)).'"');
        }

        return $response;
    }

    private function dispatch(Request $request, \Closure $next)
    {
        if (! config(static::CONFIG.'.enabled')) {
            return response()->json(['error' => 'not_found'], 404);
        }
        $public = parse_url(config(static::CONFIG.'.public_url'));
        $expectedOrigin = ($public['scheme'] ?? 'https').'://'.($public['host'] ?? '').(isset($public['port']) ? ':'.$public['port'] : '');
        if ($request->getHost() !== ($public['host'] ?? '') || ($request->headers->has('Origin') && rtrim($request->header('Origin'), '/') !== $expectedOrigin)) {
            return response()->json(['error' => 'invalid_origin'], 403);
        }
        if (strlen($request->getContent()) > 16384) {
            return response()->json(['error' => 'request_too_large'], 413);
        }
        $key = (static::PATH === '/mcp' ? 'health-mcp:' : 'mcp:'.static::PATH.':').hash('sha256', $request->ip() ?? 'unknown');
        if (RateLimiter::tooManyAttempts($key, max(1, (int) config(static::CONFIG.'.requests_per_minute')))) {
            return response()->json(['error' => 'rate_limited'], 429, ['Retry-After' => (string) RateLimiter::availableIn($key)]);
        }
        RateLimiter::hit($key, 60);
        return $next($request);
    }
}
