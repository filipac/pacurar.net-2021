<?php

return [
    'enabled' => (bool) env('MCP_TEST_ENABLED', true),
    'public_url' => env('MCP_TEST_PUBLIC_URL', rtrim(env('APP_URL', 'https://pacurar.dev'), '/').'/mcp-test'),
    'requests_per_minute' => (int) env('MCP_TEST_RATE_LIMIT', 30),
];
