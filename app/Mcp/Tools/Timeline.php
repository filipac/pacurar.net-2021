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
final class Timeline extends HealthTool
{
    protected string $name = 'health_timeline';

    protected string $description = 'Retrieve published health data for an inclusive date range of at most 365 days. Select fields/providers to reduce output; otherwise returns the complete canonical timeline. Preserves empty days, source timestamps and all observations. Never merges providers or adds workout energy to daily totals.';
}
