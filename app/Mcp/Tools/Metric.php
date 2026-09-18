<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsDestructive(false)]
#[IsIdempotent]
#[IsOpenWorld(false)]
final class Metric extends HealthTool
{
    protected string $name = 'health_metric';

    protected string $description = 'Retrieve one published health metric as a provider-specific time series. Use this instead of health_timeline when analysis requires only one metric. Preserves measurement timestamps and multiple observations. Never merges providers. Missing data is never zero.';
}
