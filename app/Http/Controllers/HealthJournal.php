<?php

namespace App\Http\Controllers;

class HealthJournal extends Controller
{
    public function archive()
    {
        global $wp_query;
        return view('health.archive', ['posts' => $wp_query->posts, 'query' => $wp_query]);
    }

    public function single()
    {
        global $post;
        return view('health.single', ['post' => $post, 'entry' => get_post_meta($post->ID, '_health_data', true)]);
    }
}
