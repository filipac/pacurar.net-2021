<?php

return [
    // The authorization server supports all these scopes; no endpoint requires all of them.
    'scopes' => [
        'mcp:use' => 'Use MCP server',
        'health' => 'Access to health data',
    ],
    'default_scopes' => ['mcp:use'],
];
