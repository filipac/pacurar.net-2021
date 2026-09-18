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
final class Latest extends HealthTool
{
    protected string $name = 'health_latest';

    protected string $description = 'Retrieve each requested metric separately for each provider on its latest available date within the last 365 days, ending today in the WordPress timezone. Returns all observations on that date, not an arbitrary intraday selection. A null date and empty observations mean none in this bounded lookback; no provider preference.';
}
