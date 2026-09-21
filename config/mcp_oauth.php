<?php

return [
    // The authorization server supports all these scopes; no endpoint requires all of them.
    'scopes' => [
        'mcp:use' => 'Use MCP server',
        'health' => 'Access to health data',
    ],
    'default_scopes' => ['mcp:use'],
    // Exact endpoint paths. Future servers do not inherit the health resource's scopes.
    'resources' => [
        '/mcp' => ['mcp:use', 'health'],
        '/mcp-test' => ['mcp:use'],
    ],
];
