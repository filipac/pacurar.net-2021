<?php

return [
    '/mcp' => [
        'server' => \App\Mcp\HealthServer::class,
        'config' => 'health_mcp',
        'scopes' => ['mcp:use', 'health'],
        // Preserve the existing rate-limit bucket across the registry migration.
        'rate_limit_prefix' => 'health-mcp:',
    ],
    '/mcp-test' => [
        'server' => \App\Mcp\TestServer::class,
        'config' => 'mcp_test',
        'scopes' => ['mcp:use'],
    ],
];
