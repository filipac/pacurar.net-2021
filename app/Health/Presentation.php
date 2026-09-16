<?php

namespace App\Health;

class Presentation
{
    public static function number($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ','), '0'), '.');
    }

    public static function value(array $metric): string
    {
        return $metric['unit'] === 'timestamp' ? (new \DateTimeImmutable('@'.(int) $metric['value']))->setTimezone(new \DateTimeZone('Europe/Bucharest'))->format('d M H:i:s') : self::number($metric['value']);
    }

    public static function prominent(array $entry): ?array
    {
        $priority = ['withings.measure.1', 'withings.measure.6', 'oura.daily_activity.steps', 'oura.sleep.average_heart_rate', 'oura.daily_sleep.score', 'oura.daily_readiness.score', 'oura.daily_spo2.spo2_percentage.average', 'oura.session.duration', 'apple_health.weight_body_mass', 'apple_health.body_fat_percentage', 'apple_health.step_count', 'apple_health.heart_rate.Avg', 'apple_health.sleep_analysis.totalSleep', 'apple_health.blood_oxygen_saturation'];
        $metrics = [];
        foreach ($entry['providers'] ?? [] as $source => $section) foreach ($section['metrics'] as $metric) if ($metric['unit'] !== 'timestamp') $metrics[] = $metric + ['source' => $source];
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
        $from = (float) (new \DateTimeImmutable($points[0]['at']))->format('U.u'); $to = (float) (new \DateTimeImmutable($points[count($points) - 1]['at']))->format('U.u');
        $path = []; $step = max(1, (int) ceil(count($points) / 720));
        foreach ($points as $i => $point) {
            if ($i % $step && $i !== count($points) - 1) continue;
            $x = 12 + 616 * ((float) (new \DateTimeImmutable($point['at']))->format('U.u') - $from) / max(1, $to - $from);
            $y = 148 - 126 * ($point['value'] - $min) / max(1, $max - $min);
            $path[] = round($x, 2).','.round($y, 2);
        }
        return ['path' => implode(' ', $path), 'min' => $min, 'max' => $max];
    }
}
