<?php

namespace App\Http\Controllers;

class HealthJournal extends Controller
{
    public function archive()
    {
        global $wp_query;
        // Journal-wide freshness stays the same across filters and pagination.
        $latest = get_posts(['post_type' => 'health_entry', 'post_status' => 'publish',
            'posts_per_page' => 1, 'orderby' => 'modified', 'order' => 'DESC',
            'suppress_filters' => true]);
        $lastUpdated = $latest ? get_post_datetime($latest[0], 'modified', 'gmt') : null;
        $lastUpdated = $lastUpdated ? $lastUpdated->setTimezone(new \DateTimeZone('Europe/Bucharest')) : null;

        return view('health.archive', ['posts' => $wp_query->posts, 'query' => $wp_query, 'lastUpdated' => $lastUpdated]);
    }

    public function single()
    {
        global $post;
        return view('health.single', ['post' => $post, 'entry' => get_post_meta($post->ID, '_health_data', true)]);
    }
}
