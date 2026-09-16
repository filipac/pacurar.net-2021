<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EnglishHealthPages
{
    public function handle(Request $request, Closure $next)
    {
        $slug = trim(config('health.archive_slug', 'health'), '/');
        $path = $request->getPathInfo();
        $isHealthPage = $path === '/'.$slug || str_starts_with($path, '/'.$slug.'/');
        // Match board-games: without WPML (including local development), use English.
        if (! $isHealthPage || ! in_array($request->method(), ['GET', 'HEAD'], true)
            || ! defined('ICL_LANGUAGE_CODE') || ICL_LANGUAGE_CODE === 'en') {
            return $next($request);
        }

        $query = $request->query();
        // Do not carry a Romanian language override onto the English domain.
        unset($query['lang']);
        $url = 'https://pacurar.dev'.$path;
        if ($query) {
            $url .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        return new RedirectResponse($url);
    }
}
