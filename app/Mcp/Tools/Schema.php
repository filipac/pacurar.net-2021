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
final class Schema extends HealthTool
{
    protected string $name = 'health_schema';

    protected string $description = 'Retrieve the authoritative public health schema: metric paths, units, provider identifiers, value shapes, source datasets, provenance and timestamp semantics. Call this before choosing metrics; it is the same schema as the WordPress health REST API.';
}
