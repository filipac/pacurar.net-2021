<?php

namespace App\Health;

class Presentation
{
    public static function number($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ','), '0'), '.');
    }

    public static function prominent(array $entry): ?array
    {
        $priority = ['withings.measure.1', 'withings.measure.6', 'oura.daily_activity.steps', 'oura.sleep.average_heart_rate', 'oura.daily_sleep.score', 'oura.daily_readiness.score', 'oura.daily_spo2.spo2_percentage.average', 'oura.session.duration'];
        $metrics = [];
        foreach ($entry['providers'] ?? [] as $source => $section) foreach ($section['metrics'] as $metric) $metrics[] = $metric + ['source' => $source];
        usort($metrics, function ($a, $b) use ($priority) {
            $aIndex = array_search($a['key'], $priority, true); $bIndex = array_search($b['key'], $priority, true);
            return ($aIndex === false ? 999 : $aIndex) <=> ($bIndex === false ? 999 : $bIndex);
        });
        return $metrics[0] ?? null;
    }

    public static function chart(array $points): array
    {
        $values = array_column($points, 'value');
        $min = min($values); $max = max($values);
        $from = strtotime($points[0]['at']); $to = strtotime($points[count($points) - 1]['at']);
        $path = []; $step = max(1, (int) ceil(count($points) / 720));
        foreach ($points as $i => $point) {
            if ($i % $step && $i !== count($points) - 1) continue;
            $x = 12 + 616 * (strtotime($point['at']) - $from) / max(1, $to - $from);
            $y = 148 - 126 * ($point['value'] - $min) / max(1, $max - $min);
            $path[] = round($x, 2).','.round($y, 2);
        }
        return ['path' => implode(' ', $path), 'min' => $min, 'max' => $max];
    }
}
