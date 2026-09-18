<?php

namespace App\Health;

/** Keep these read-only exports live, including validation/error responses. */
class AnalyticsNoCache
{
    public static function boot(): void
    {
        if (self::isRequest()) self::disable();
        add_filter('w3tc_can_cache', static fn ($allowed) => self::isRequest() ? false : $allowed, PHP_INT_MAX);
        add_filter('w3tc_config_item_pgcache.reject.uri', [self::class, 'rejectedUris']);
        add_filter('rest_pre_dispatch', static function ($result, $server, $request) {
            if (self::route($request->get_route())) self::disable();
            return $result;
        }, PHP_INT_MAX, 3);
        add_filter('rest_post_dispatch', static function ($response, $server, $request) {
            if (self::route($request->get_route())) {
                foreach (self::headers() as $key => $value) $response->header($key, $value);
            }
            return $response;
        }, PHP_INT_MAX, 3);
    }

    public static function headers(): array
    {
        return ['Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'CDN-Cache-Control' => 'no-store', 'Cloudflare-CDN-Cache-Control' => 'no-store',
            'Pragma' => 'no-cache', 'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT'];
    }

    public static function rejectedUris(array $uris): array
    {
        $uris[] = '/'.preg_quote(rest_get_url_prefix(), '~').'/health/v1/(timeline|schema)(/|[?]|$)';
        $uris[] = '[?&]rest_route=(%2F|/)health(%2F|/)v1(%2F|/)(timeline|schema)(%2F|/|&|$)';
        $uris[] = '^/mcp(/|[?]|$)';
        return array_values(array_unique($uris));
    }

    public static function isRequest(): bool
    {
        $queryRoute = $_GET['rest_route'] ?? null;
        if (is_string($queryRoute) && self::route($queryRoute)) return true;
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        if (is_string($path) && rtrim($path, '/') === '/mcp') return true;
        return is_string($path) && (bool) preg_match('~/(?:'.preg_quote(rest_get_url_prefix(), '~').')/health/v1/(timeline|schema)/?$~D', rawurldecode($path));
    }

    private static function route(string $route): bool
    {
        return in_array(rtrim($route, '/'), ['/health/v1/timeline', '/health/v1/schema'], true);
    }

    private static function disable(): void
    {
        foreach (['DONOTCACHEPAGE', 'DONOTCACHEDB', 'DONOTCACHEOBJECT'] as $constant) if (! defined($constant)) define($constant, true);
    }
}
