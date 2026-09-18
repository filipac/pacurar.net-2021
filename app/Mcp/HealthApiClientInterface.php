<?php

namespace App\Mcp;

interface HealthApiClientInterface
{
    public function schema(): array;

    public function timeline(string $from, string $to): array;
}
