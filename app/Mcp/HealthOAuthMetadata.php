<?php

namespace App\Mcp;

use App\Health\AnalyticsNoCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

/** Extend SDK discovery responses without modifying vendor code. */
final class HealthOAuthMetadata
{
    public function handle(Request $request, \Closure $next)
    {
        $registration = $request->isMethod('POST');
        $requestedScopes = null;
        if ($registration && $request->exists('scope')) {
            $scope = $request->input('scope');
            if (!is_string($scope) || strlen($scope) > 2000) {
                return response()->json(['error' => 'invalid_scope'], 400)->withHeaders(AnalyticsNoCache::headers());
            }
            $requestedScopes = array_values(array_unique(explode(' ', trim($scope))));
            if ($scope === '' || array_diff($requestedScopes, array_keys(Passport::$scopes))) {
                return response()->json(['error' => 'invalid_scope'], 400)->withHeaders(AnalyticsNoCache::headers());
            }
        }
        $response = $next($request);
        if ($response instanceof JsonResponse && $response->isSuccessful()) {
            $metadata = $response->getData(true);
            if ($response->getStatusCode() === 201 && isset($metadata['client_id'])) {
                // Registration is shared: never assign another resource's scopes by default.
                // Without a requested scope, leave resource discovery to select the scopes.
                unset($metadata['scope']);
                if ($requestedScopes !== null) $metadata['scope'] = implode(' ', $requestedScopes);
            } elseif (isset($metadata['resource'])) {
                $metadata['scopes_supported'] = self::resourceScopes((string) parse_url($metadata['resource'], PHP_URL_PATH));
            } elseif (isset($metadata['issuer'])) {
                $metadata['scopes_supported'] = array_keys(Passport::$scopes);
            }
            $response->setData($metadata);
        }
        foreach (AnalyticsNoCache::headers() as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    public static function resourceScopes(string $path): array
    {
        return config('mcp_oauth.resources')[rtrim($path, '/') ?: '/']
            ?? config('mcp_oauth.default_scopes');
    }
}
