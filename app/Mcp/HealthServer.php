<?php

namespace App\Mcp;

final class HealthServer extends \Laravel\Mcp\Server
{
    protected string $name = 'pacurar.dev public health';

    protected string $version = '1.0.0';

    protected string $instructions = 'Read-only access to intentionally published health data. Start with health_schema for field definitions. Prefer health_metric or filtered health_timeline to minimize data transfer. Preserve providers, repeated observations and source timestamps. Apple midnight bucket timestamps are not evidence of physical measurement time. Missing is not zero. Latest searches at most 365 days. These tools supply data and descriptive statistics, not medical interpretations.';

    protected array $capabilities = [self::CAPABILITY_TOOLS => ['listChanged' => false]];

    protected array $tools = [
        Tools\Schema::class,
        Tools\Timeline::class,
        Tools\Metric::class,
        Tools\Latest::class,
        Tools\Workouts::class,
        Tools\Summary::class,
        Tools\CurrentUser::class
    ];
}
