<?php

namespace App\Http\Controllers;

class HealthJournal extends Controller
{
    public function compare(\Illuminate\Http\Request $request)
    {
        $metrics = \App\Health\Trends::published();
        $today = new \DateTimeImmutable('today', new \DateTimeZone('Europe/Bucharest'));
        $range = $request->query('range', '30');
        if (! in_array($range, ['7', '30', '90', 'all', 'custom'], true)) $range = '30';
        $dates = [];
        foreach ($metrics as $metric) $dates = array_merge($dates, array_keys($metric['days']));
        $from = $range === 'all' && $dates ? min($dates) : $today->modify('-'.((int) (is_numeric($range) ? $range : 30) - 1).' days')->format('Y-m-d');
        $to = $today->format('Y-m-d');
        $error = null;
        if ($range === 'custom') {
            $start = $request->query('from'); $end = $request->query('to');
            if (! \App\Health\Trends::validDate($start) || ! \App\Health\Trends::validDate($end) || $start > $end) {
                $error = 'Choose a valid start and end date, with the start before the end.';
            } else { $from = $start; $to = $end; }
        }
        $choice = $request->query('metric', isset($metrics['withings.measure.1|daily']) ? 'withings.measure.1|daily' : array_key_first($metrics));
        $selected = [];
        if ($choice !== null) {
            if (! is_string($choice) || ! isset($metrics[$choice])) $error = 'Choose one metric from the published measurements.';
            else $selected = [$choice];
        }
        $charts = [];
        if (! $error) foreach ($selected as $key) $charts[$key] = \App\Health\Trends::chart($metrics[$key], $from, $to);
        return view('health.compare', compact('metrics', 'selected', 'charts', 'from', 'to', 'range', 'error'));
    }

    public function archive()
    {
        global $wp_query;
        // Journal-wide freshness stays the same across filters and pagination.
        $latest = get_posts(['post_type' => 'health_entry', 'post_status' => 'publish',
            'posts_per_page' => 1, 'orderby' => 'modified', 'order' => 'DESC',
            'suppress_filters' => true]);
        $lastUpdated = $latest ? get_post_datetime($latest[0], 'modified', 'gmt') : null;
        $lastUpdated = $lastUpdated ? $lastUpdated->setTimezone(new \DateTimeZone('Europe/Bucharest')) : null;

        $entries = [];
        foreach ($wp_query->posts as $healthPost) $entries[$healthPost->ID] = get_post_meta($healthPost->ID, '_health_data', true);
        return view('health.archive', ['posts' => $wp_query->posts, 'query' => $wp_query, 'lastUpdated' => $lastUpdated,
            'entries' => $entries, 'comparisons' => new \App\Health\Comparisons($entries),
            'overview' => \App\Health\Overview::latest()]);
    }

    public function single()
    {
        global $post;
        $entry = get_post_meta($post->ID, '_health_data', true);
        return view('health.single', ['post' => $post, 'entry' => $entry, 'comparisons' => new \App\Health\Comparisons([$entry])]);
    }
}
