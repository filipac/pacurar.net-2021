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
final class Workouts extends HealthTool
{
    protected string $name = 'health_workouts';

    protected string $description = 'Retrieve individual published workouts within an inclusive date range of at most 365 days. Optional exact type/provider/origin filters. Preserves every published field and repeated numerical observations. Cross-provider copies are not deduplicated; do not add workout calories to daily active energy.';
}
