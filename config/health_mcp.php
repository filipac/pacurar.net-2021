<?php

return [
    // Registered in Passport and advertised by both OAuth discovery documents.
    'scopes' => [
        'mcp:use' => 'Use MCP server',
        'health' => 'Access to health data',
    ],
    'enabled' => (bool) env('HEALTH_MCP_ENABLED', true),
    'public_url' => env('HEALTH_MCP_PUBLIC_URL', rtrim(env('APP_URL', 'https://pacurar.dev'), '/').'/mcp'),
    'requests_per_minute' => (int) env('HEALTH_MCP_RATE_LIMIT', 30),
    'max_response_bytes' => (int) env('HEALTH_MCP_MAX_RESPONSE_BYTES', 262144),
];
