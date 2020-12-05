<?php

namespace App\Providers;

use App\Jobs\CreateOgImageJob;
use App\Models\Wp\Post\Post;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Change the error reporting level to match WordPress's
        if (! WP_DEBUG) {
            error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);
        }

        $this->shareViewData();

        add_action('publish_post', function ($id, $post_obj) {
            $post = new Post($id);
            $job = new CreateOgImageJob($post);
            dispatch($job);
        }, 10, 2);


//        add_filter( 'query_vars', function ( $vars ) {
//            $vars[] = 'custom_page';
//            return $vars;
//        } );
//
//        add_action('init', function () {
//            add_rewrite_rule('^api/?','index.php?page_id='.get_option( 'page_for_posts' ).'&custom_page=api','top');
//        }, 10, 0);
    }

    public function shareViewData()
    {
        add_filter('template_include', function ($template) {

            // Share global view data
            // view()->share('data_name', 'data');

            // Sharing data with specific view
            // view()->composer(['components.sidebar'], 'App\Http\ViewComposers\SidebarComposer');

            return $template;
        });


        add_filter('the_content', function ($content) {
            if (is_single()) {
                if (has_tag('azi-am-invatat')):
            $content .= '<p class="block w-full bg-green-200 p-4">Vezi ce alte lucruri am mai invatat in alte zile urmarind postari similare: <a href="/tag/azi-am-invatat/">toate postarile "azi am invatat"</a>.</p>';
                endif;
            }
            return $content;
        }, 0);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        add_action('template_redirect', function () {
            if (is_search()) {
                if (isset($_GET['submit']) && $_GET['submit'] = 'Ma+simt+baftos') {
                    if (Post::queriedPosts()->count() > 0) {
                        $post = Post::queriedPosts()->first();
                        if ($post instanceof Post) {
                            wp_redirect(get_permalink($post->wpPost()));
                            die;
                        }
                    }
                }
                $randomNaviColor = function () {
                    return collect([
                        'bg-blue-600 text-white',
                        'bg-red-600  text-white',
                        'bg-yellow',
                        'bg-green-600',
                    ])->random(2)->first();
                };
                add_action('wp_pagenavi_class_pages', $randomNaviColor);
                add_action('wp_pagenavi_class_first', $randomNaviColor);
                add_action('wp_pagenavi_class_previouspostslink', $randomNaviColor);
                add_action('wp_pagenavi_class_extend', $randomNaviColor);
                add_action('wp_pagenavi_class_smaller', $randomNaviColor);
                add_action('wp_pagenavi_class_page', $randomNaviColor);
                add_action('wp_pagenavi_class_current', $randomNaviColor);
                add_action('wp_pagenavi_class_larger', $randomNaviColor);
                add_action('wp_pagenavi_class_nextpostslink', $randomNaviColor);
                add_action('wp_pagenavi_class_last', $randomNaviColor);
            }
        });
    }
}
