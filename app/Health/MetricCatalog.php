<?php

namespace App\Health;

class MetricCatalog
{
    public const TOPICS = ['weight' => 'Weight', 'body-composition' => 'Body composition', 'activity' => 'Activity', 'heart' => 'Heart', 'sleep' => 'Sleep', 'recovery' => 'Recovery', 'vitals' => 'Vitals', 'mindfulness' => 'Mindfulness'];

    public static function all(): array
    {
        static $catalog;

        return $catalog ??= json_decode(file_get_contents(__DIR__.'/metrics.json'), true, flags: JSON_THROW_ON_ERROR);
    }
}
