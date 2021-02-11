<?php

namespace App\Providers;

use App\Jobs\CreateOgImageJob;
use App\Models\Wp\Post\Post;
use Automattic\Jetpack\Jetpack_Lazy_Images;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

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
        if (!WP_DEBUG) {
            error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);
        }
        if (!session_id()) {
            session_start();
        }

        // dd(session()->getId(), session_id());

        if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'en') {
            config()->set('app.url', 'https://pacurar.dev');
        }

        // dd(site_url());

        add_action('wp_loaded', function () {
            if ($_SESSION['set_lang']  == '1') {
                return ;
            }

            $country = request()->headers->get('CF-IPCountry', 'US');
            if ($country == 'RO') {
                $language = 'ro';
            } else {
                $language = 'en';
            }
            if (!isset($_SESSION['lang'])) {
                $_SESSION['lang'] = $language;
                global $sitepress;
                global $sitepress;

                // wp_redirect();

                // $sitepress->set_default_language($language);
                // $sitepress->switch_lang($language);
            }

            $_SESSION['set_lang'] = 1;
        });

        add_filter('jetpack_relatedposts_filter_enabled_for_request', function ($enable) {
            if (is_single()) {
                $post = get_post();
                $info = wpml_get_language_information(null, $post->ID);
                if (is_array($info) && isset($info['language_code']) && $info['language_code'] == 'en') {
                    return false;
                }
            }
            return $enable;
        });

        add_shortcode('alpine', function ($atts, $content = null) {
            $a = shortcode_atts(array(
                'wrap' => 'div',
            ), $atts);
            $alpineAttrs = [];
            if (is_array($atts)) {
                $alpineAttrs = array_diff($atts, $a);
            }
            $alpineAttrs = str_replace("=", '="', http_build_query($alpineAttrs, '', '" ', PHP_QUERY_RFC3986)).'"';
            // dd($alpineAttrs);
            ob_start();
            echo '<'.$a['wrap'].' '.$alpineAttrs.'>';
            echo wpautop($content);
            echo '</'.$a['wrap'].'>';
            return ob_get_clean();
        });


        $this->shareViewData();

        add_action('publish_post', function ($id, $post_obj) {
            $post = new Post($id);
            if ($post->wpPost()->post_type == 'work') {
                return;
            }
            $job = new CreateOgImageJob($post);
            dispatch($job);
        }, 10, 2);

        add_filter('manage_posts_columns', function ($columns) {
            $columns['og_image'] = 'OG';
            return $columns;
        });
        add_filter('manage_page_posts_columns', function ($columns) {
            $columns['og_image'] = 'OG';
            return $columns;
        });

        $og = function ($column_id, $post_id) {
            switch ($column_id) {
                case 'og_image':
                    $imgId = get_post_meta($post_id, 'og_image', true);
                    if ($imgId && ($data = wp_get_attachment_image_src($imgId, 'full')) && is_array($data)) {
                        echo "<div><img src='{$data[0]}' style='max-width: 100px;' /></div>";
                    }
                    echo "<div><a href='/wp-admin/options-general.php?page=fpac_regen_og&id={$post_id}' class='btn btn-primary'>Regenerate</a></div>";
                    break;

            }
        };

        add_action('manage_posts_custom_column', $og, 10, 2);
        add_action('manage_page_posts_custom_column', $og, 10, 2);

        add_action('manual_lazy_image', function ($content) {
            $inst = Jetpack_Lazy_Images::instance();
            $content = $inst->add_image_placeholders($content);

            return $content;
        });

        /**
         * Fires before determining which template to load.
         * Ensure work page is available only in English
         */
        add_action('template_redirect', function () : void {
            global $wp;
            if ($wp->request == 'my-work') {
                if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'ro') {
                    $url = apply_filters('wpml_permalink', get_bloginfo('url').'/my-work', 'en');
                    wp_redirect($url, Response::HTTP_MOVED_PERMANENTLY);
                    die;
                }
            }
        });


        add_action('admin_menu', function () {

            // Register the hidden submenu.
            add_submenu_page(
                null,
                'Regenerate og image',
                '',
                'manage_options',
                'fpac_regen_og',
                function () {
                    if (!request()->get('id')) {
                        return;
                    }
                    $id = request()->get('id');
                    $post = get_post((int) $id);
                    if (!$post) {
                        return;
                    }
                    $post = new Post($id);
                    $job = new CreateOgImageJob($post);
                    $job->forced()->handle();
                    $resp = redirect()->to(request()->headers->get('referer', '/wp-admin/edit.php'));
                    return $resp->send();
                }
            );
        });


//        dd(get_stylesheet_directory_uri().'/resources/v1.mp3');


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
        $this->app->register(CustomFieldsServiceProvider::class);
        $this->app->register(CustomPostTypesProvider::class);
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
