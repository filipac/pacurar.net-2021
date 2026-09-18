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
final class Summary extends HealthTool
{
    protected string $name = 'health_summary';

    protected string $description = 'Calculate descriptive statistics for one numerical metric and one provider over at most 365 inclusive days. Counts all observations, lists missing days, and returns min/max/mean/median/first/last/numeric change. No health interpretation, medical scores, correlations or causal conclusions. Use health_metric for original observations or clock times.';
}
