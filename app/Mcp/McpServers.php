<?php

namespace App\Mcp;

/** One registry for routing, transport settings, resource scopes and WordPress exclusions. */
final class McpServers
{
    public static function all(): array
    {
        return config('mcp_servers', []);
    }

    public static function find(string $path): ?array
    {
        $path = rtrim($path, '/') ?: '/';
        $server = self::all()[$path] ?? null;

        return $server === null ? null : ['path' => $path] + $server;
    }

    public static function isRequest(): bool
    {
        return self::find((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH)) !== null;
    }
}
