<?php

namespace App\Providers;

use Laraish\Support\Wp\Providers\ThemeOptionsProvider as ProvidersThemeOptionsProvider;

class ThemeOptionsProvider extends ProvidersThemeOptionsProvider
{
    public function boot()
    {
        //

        app()->bind('the_author', function () {
            global $authordata;
            if (! is_object($authordata)) {
                return '';
            }

            $link = sprintf(
                '<a href="%1$s" title="%2$s" rel="author" class="transform-all hover:font-black hover:underline">%3$s</a>',
                esc_url(get_author_posts_url($authordata->ID, $authordata->user_nicename)),
        /* translators: %s: Author's display name. */
        esc_attr(sprintf(__('Posts by %s'), get_the_author())),
                get_the_author()
            );

            /**
             * Filters the link to the author page of the author of the current post.
             *
             * @since 2.9.0
             *
             * @param string $link HTML link.
             */
            return apply_filters('the_author_posts_link', $link);
        });

        parent::boot();
    }
}
