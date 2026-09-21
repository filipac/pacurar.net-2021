<?php

namespace App\Mcp;

use App\Health\AnalyticsNoCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Extend SDK discovery responses without modifying vendor code. */
final class HealthOAuthMetadata
{
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);
        if ($response instanceof JsonResponse && $response->isSuccessful()) {
            $metadata = $response->getData(true);
            $metadata['scopes_supported'] = array_values(array_unique(array_merge(
                $metadata['scopes_supported'] ?? [], array_keys(config('health_mcp.scopes'))
            )));
            $response->setData($metadata);
        }
        foreach (AnalyticsNoCache::headers() as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
