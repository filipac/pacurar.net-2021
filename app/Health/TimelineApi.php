<?php

namespace App\Health;

class TimelineApi
{
    public const MAX_DAYS = 365;
    public const BATCH_SIZE = 8;

    public function timeline(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $timezone = wp_timezone();
        try { [$from, $to, $count] = self::range($request->get_query_params(), new \DateTimeImmutable('now', $timezone)); }
        catch (\InvalidArgumentException $error) { return new \WP_Error('health_invalid_range', $error->getMessage(), ['status' => 400]); }

        global $wpdb;
        $normalizer = new TimelineNormalizer($timezone);
        $cursor = 0;
        // Keyset batches keep even a year's ECG-heavy entries out of the WP
        // object cache and bound peak memory. No per-post or per-metric queries.
        do {
            $rows = $wpdb->get_results($wpdb->prepare("SELECT m.meta_id, m.meta_value, d.meta_value AS day
                FROM {$wpdb->postmeta} m
                JOIN {$wpdb->posts} p ON p.ID = m.post_id
                JOIN {$wpdb->postmeta} d ON d.post_id = p.ID AND d.meta_key = '_health_date'
                WHERE m.meta_key = '_health_data' AND m.meta_id > %d
                AND p.post_type = 'health_entry' AND p.post_status = 'publish' AND p.post_password = ''
                AND d.meta_value BETWEEN %s AND %s
                ORDER BY m.meta_id ASC LIMIT %d", $cursor, $from, $to, self::BATCH_SIZE));
            if ($wpdb->last_error) return new \WP_Error('health_read_failed', 'Published health data could not be read.', ['status' => 503]);
            foreach ($rows as $row) {
                $cursor = (int) $row->meta_id;
                $entry = @unserialize($row->meta_value, ['allowed_classes' => false]);
                if (is_array($entry) && ($entry['date'] ?? null) === $row->day) $normalizer->add($entry);
            }
            unset($entry);
        } while (count($rows) === self::BATCH_SIZE);

        $days = [];
        $start = new \DateTimeImmutable($from, $timezone);
        for ($offset = 0; $offset < $count; $offset++) $days[] = $normalizer->day($start->modify('+'.$offset.' days')->format('Y-m-d'));
        return new \WP_REST_Response(['meta' => ['schema_version' => AnalyticsCatalog::VERSION, 'from' => $from, 'to' => $to,
            'timezone' => $timezone->getName(), 'days' => $count, 'schema_url' => rest_url('health/v1/schema')], 'days' => $days], 200, AnalyticsNoCache::headers());
    }

    public static function range(array $params, \DateTimeImmutable $now): array
    {
        if (array_diff(array_keys($params), ['from', 'to', 'days', 'rest_route', '_fields'])) throw new \InvalidArgumentException('Supported range parameters are from, to and days.');
        $hasFrom = array_key_exists('from', $params); $hasTo = array_key_exists('to', $params);
        if ($hasFrom || $hasTo) {
            if (! $hasFrom || ! $hasTo || array_key_exists('days', $params)) throw new \InvalidArgumentException('Supply both from and to, or days, not both forms.');
            if (! Trends::validDate($params['from']) || ! Trends::validDate($params['to']) || substr($params['from'], 0, 4) === '0000' || substr($params['to'], 0, 4) === '0000') throw new \InvalidArgumentException('Dates must be valid YYYY-MM-DD calendar dates.');
            $from = $params['from']; $to = $params['to'];
            if ($from > $to) throw new \InvalidArgumentException('from must be on or before to.');
            $count = (new \DateTimeImmutable($from, new \DateTimeZone('UTC')))->diff(new \DateTimeImmutable($to, new \DateTimeZone('UTC')))->days + 1;
        } else {
            $value = $params['days'] ?? '30';
            if ((! is_int($value) && ! is_string($value)) || ! preg_match('/^[1-9][0-9]{0,2}$/D', (string) $value)) throw new \InvalidArgumentException('days must be an integer from 1 to 365.');
            $count = (int) $value;
            $to = $now->format('Y-m-d');
            $from = $now->modify('-'.($count - 1).' days')->format('Y-m-d');
        }
        if ($count > self::MAX_DAYS) throw new \InvalidArgumentException('The maximum inclusive range is 365 days.');
        return [$from, $to, $count];
    }
}
