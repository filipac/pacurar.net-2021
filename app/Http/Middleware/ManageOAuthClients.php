<?php

namespace App\Http\Middleware;

use App\Health\AnalyticsNoCache;
use Illuminate\Http\Request;

final class ManageOAuthClients
{
    public function handle(Request $request, \Closure $next)
    {
        if (!is_user_logged_in()) {
            $response = $request->isMethod('GET') && !$request->expectsJson()
                ? redirect()->guest(wp_login_url($request->fullUrl()))
                : response()->json(['message' => 'WordPress login is required.'], 401);
        } elseif (!current_user_can('manage_options')) {
            $response = $request->expectsJson()
                ? response()->json(['message' => 'WordPress administrator access is required.'], 403)
                : response('WordPress administrator access is required.', 403);
        } else {
            $response = $next($request);
        }
        foreach (AnalyticsNoCache::headers() as $key => $value) $response->headers->set($key, $value);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
