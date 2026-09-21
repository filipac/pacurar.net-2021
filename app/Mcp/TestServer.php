<?php

namespace App\Mcp;

final class TestServer extends \Laravel\Mcp\Server
{
    protected string $name = 'pacurar.dev OAuth test';
    protected string $version = '1.0.0';
    protected string $instructions = 'Test OAuth authentication by returning only the authenticated WordPress user email and username. This server provides no health tools and does not require the health scope.';
    protected array $capabilities = [self::CAPABILITY_TOOLS => ['listChanged' => false]];
    protected array $tools = [Tools\CurrentUser::class];
}
