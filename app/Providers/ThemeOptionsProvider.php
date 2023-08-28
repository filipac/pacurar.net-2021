<?php

namespace App\Providers;

use LaraWelP\Foundation\Support\Wp\Providers\ThemeOptionsProvider as ProvidersThemeOptionsProvider;

class ThemeOptionsProvider extends ProvidersThemeOptionsProvider
{
    public function boot(): void
    {
        //

        app()->bind('the_author', function () {
            global $authordata;
            if (!is_object($authordata)) {
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
             * @param string $link HTML link.
             *
             * @since 2.9.0
             *
             */
            return apply_filters('the_author_posts_link', $link);
        });

        parent::boot();
    }

    public function register(): void
    {
        add_theme_support('post-formats', [
            'aside',
        ]);

        add_action('save_post', function ($postId) {
            if (has_post_format('aside', $postId)) {
                if (!has_term('Status Update', 'category', $postId)) {
                    $term = get_term_by('slug', 'status-update', 'category');
                    if (!$term) {
                        $term = wp_insert_term('Status Update', 'category');
                    }
                    wp_set_post_categories($postId, [$term->term_id]);
                }
                if (!has_tag('status-update', $postId)) {
                    wp_set_post_tags($postId, 'scurte', true);
                }
                if (!has_tag('aside', $postId)) {
                    wp_set_post_tags($postId, 'aside', true);
                }
                $title = get_the_title($postId);
                if (!$title) {
                    // set the title to the first 5 words of the content with ... suffix
                    $content = get_post_field('post_content', $postId);
                    $content = strip_tags($content);
                    if (strlen($content) > 5) {
                        $content = preg_replace('/\s+/', ' ', $content);
                        $content = explode(' ', $content);
                        $title   = implode(' ', array_slice($content, 0, 5));
                        $title   .= '...';
                        wp_update_post([
                            'ID'         => $postId,
                            'post_title' => $title,
                        ]);
                    }
                }
            }
        });
    }
}
