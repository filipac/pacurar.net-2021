<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomPostTypesProvider extends ServiceProvider
{
    public function boot()
    {
        add_action('init', function () {
            register_extended_post_type('work', [
                'show_in_rest' => true,
                'has_archive' => false,
                'supports' => ['title', 'editor', 'thumbnail'],
                'show_ui' => true,
            ], [
                'singular' => 'My Work Item',
                'plural' => 'My Work Items',
                'slug' => 'my-work'
            ]);

            register_extended_taxonomy('technology', 'work', [
                'show_in_rest'               => true,
                'has_archive' => false,
                'public' => false,
                'show_ui' => true,
            ], array(

                    # Override the base names used for labels:
                    'singular' => 'Technology',
                    'plural'   => 'Technologies',

                ));
        });
    }
}
