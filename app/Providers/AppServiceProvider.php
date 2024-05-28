<?php

namespace App\Providers;

use App\GraphQL\Mutations\VerifyLogin;
use App\Livewire\BoardGames;
use App\Jobs\CalculateStreak;
use App\Jobs\CreateOgImageJob;
use App\Models\Wp\Post\Post;
use Automattic\Jetpack\Jetpack_Lazy_Images;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Routing\Pipeline;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Peerme\Mx\Address;
use Peerme\MxLaravel\Multiversx;
use Peerme\MxProviders\Entities\Account;
use Peerme\MxProviders\Exceptions\RequestException;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Change the error reporting level to match WordPress's
        if (!WP_DEBUG) {
            error_reporting(E_CORE_ERROR
                | E_CORE_WARNING
                | E_COMPILE_ERROR
                | E_ERROR
                | E_WARNING
                | E_PARSE
                | E_USER_ERROR
                | E_USER_WARNING
                | E_RECOVERABLE_ERROR);
        }
        if (!session_id() && !app()->runningInConsole() && !headers_sent()) {
            session_start();
        }

        add_filter('acf/settings/remove_wp_meta_box', '__return_false');

        if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'en') {
            config()->set('app.url', 'https://pacurar.dev');
        }

        add_action('init', function () {
            if (is_admin()) {
                return;
            }

            $pipe = app(Pipeline::class);

            $req = request();
            $pipe->send($req)
                ->through([
                    StartSession::class,
                    function ($request, $next) {
                        $response = new Response();
                        return $next($response);
                    },
                ])
                ->thenReturn();
            request()->setLaravelSession(session());
//            dd($req, session());

            if ($req->hasSession()) {
                if (request()->get('address') && request()->get('signature')) {

                    (new VerifyLogin())(null, [
                        'address'   => request()->get('address'),
                        'signature' => request()->get('signature'),
                    ]);

                }
            }
        }, 1);

        // prepend every page content with a div
        add_filter('the_content', function ($content) {
            if (is_admin() || !is_page() && get_post()->post_name != 'uses') {
                return $content;
            }

            if (is_page()) {
                $noAds = get_post_meta(get_the_ID(), 'no-ads', true);
                if ($noAds && $noAds == '1') {
                    return $content;
                }
            }

            $cc = Blade::render('<x-web3-ad spaceName="page-top" format="dark" />');

            return '<div class="web3">' . $cc . '</div>' . $content;
        }, 999);

        add_filter('the_content', function ($content) {
            if (is_admin() || !is_single()) {
                return $content;
            }

            $is_behind_paywall = get_post_meta(get_the_ID(), 'is_behind_paywall', true);
            if ($is_behind_paywall) {
                if (!auth('wordpress')->user()) {
                    $content = '<div data-web3-state-management data-login="1"></div>';
                } else {
                    $content = $content . '<div data-web3-state-management data-login="1"></div>';
                }
            }

//            if(auth()->id()) {
//                $content = $content . get_user_meta(auth()->id(), 'wallet', true);
//            }


            return $content;
        }, 999);

        \add_filter('oembed_response_data', function ($embedData, $post) {
            $imgId = get_post_meta($post->ID, 'og_image', true);
            if ($imgId) {
                if (($data = wp_get_attachment_image_src($imgId, 'full')) && is_array($data)) {
                    $embedData['thumbnail_url'] = $data[0];
                }
            }
            return $embedData;
        }, 11, 2);

        // dd(site_url());

        add_action('wp_loaded', function () {
            if (isset($_SESSION['set_lang']) && $_SESSION['set_lang'] == '1') {
                return;
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
            $a           = shortcode_atts([
                'wrap' => 'div',
            ], $atts);
            $alpineAttrs = [];
            if (is_array($atts)) {
                $alpineAttrs = array_diff($atts, $a);
            }
            $alpineAttrs = str_replace("=", '="', http_build_query($alpineAttrs, '', '" ', PHP_QUERY_RFC3986)) . '"';
            // dd($alpineAttrs);
            ob_start();
            echo '<' . $a['wrap'] . ' ' . $alpineAttrs . '>';
            echo wpautop($content);
            echo '</' . $a['wrap'] . '>';
            return ob_get_clean();
        });


        $this->shareViewData();

        add_action('publish_post', function ($id, $post_obj) {
            $post = new Post($id);
            if ($post->wpPost()->post_type == 'work') {
                return;
            }
            $job = new CreateOgImageJob($post);
            dispatch(new CalculateStreak())->chain([
                $job,
            ]);
        }, 10, 2);

        add_action('w3tc_flush_all', function ($flush) {
            Artisan::call('cache:clear');
        });

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
                        echo "<div>
                            <a href='{$data[0]}' target='_blank'>
                                <img src='{$data[0]}' style='max-width: 100px;' />
                            </a>
                        </div>";
                    }
                    echo "<div><a href='/wp-admin/options-general.php?page=fpac_regen_og&id={$post_id}' class='btn btn-primary'>Regenerate</a></div>";
                    break;
            }
        };

        add_action('manage_posts_custom_column', $og, 10, 2);
        add_action('manage_page_posts_custom_column', $og, 10, 2);

        add_action('manual_lazy_image', function ($content) {
            if (!class_exists(Jetpack_Lazy_Images::class)) {
                return $content;
            }
            $inst = Jetpack_Lazy_Images::instance();
            if (!$inst) {
                return $content;
            }
            $content = $inst->add_image_placeholders($content);

            return $content;
        });

        /**
         * Fires before determining which template to load.
         * Ensure work page is available only in English
         */
        add_action('template_redirect', function (): void {
            global $wp;
            if ($wp->request == 'my-work') {
                if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'ro') {
                    $url = apply_filters('wpml_permalink', get_bloginfo('url') . '/my-work', 'en');
                    wp_redirect($url, Response::HTTP_MOVED_PERMANENTLY);
                    die;
                }
            } else {
                if ($wp->request == 'uses') {
                    if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'ro') {
                        $url = apply_filters('wpml_permalink', get_bloginfo('url') . '/uses', 'en');
                        wp_redirect($url, Response::HTTP_MOVED_PERMANENTLY);
                        die;
                    }
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
                    $id   = request()->get('id');
                    $post = get_post((int)$id);
                    if (!$post) {
                        return;
                    }
                    $post = new Post($id);
                    $job  = new CreateOgImageJob($post);
                    $job->forced()->handle();
                    $resp = redirect()->to(request()->headers->get('referer', '/wp-admin/edit.php'));
                    return $resp->send();
                }
            );

            add_submenu_page(
                null,
                'Call calculate',
                '',
                'manage_options',
                'fpac_calc_man',
                function () {
                    CalculateStreak::dispatch();
                }
            );
        });

        // custom rest endpoint to list my board games
        add_action('rest_api_init', function () {
            register_rest_route('filipac/v1', '/board-games', [
                'methods'             => 'GET',
                'permission_callback' => '__return_true', // 'is_user_logged_in
                'callback'            => function () {
                    $games = BoardGames::getNfts();
                    return new \WP_REST_Response($games->toArray(), 200);
                },
            ]);
            register_rest_route('filipac/v1', '/work', [
                'permission_callback' => '__return_true', // 'is_user_logged_in
                'methods'             => 'GET',
                'callback'            => function () {
                    $query = [
                        'post_type' => 'work',
                        'nopaging'  => true,
                        'tax_query' => ['relation' => 'AND'],
                        'orderby'   => 'menu_order',
                        'order'     => 'ASC',
                    ];
                    $q     = new \WP_Query($query);
                    $posts = collect($q->posts)->map(function ($post) {
                        $categories = get_the_terms($post, 'technology') ?: [];
                        return [
                            'title'      => $post->post_title,
                            'image'      => get_the_post_thumbnail_url($post->ID, 'full'),
                            'categories' => collect($categories)->map(function ($category) {
                                return $category->name;
                            })->implode(', '),
                        ];
                    });
                    return new \WP_REST_Response($posts, 200);
                },
            ]);
        });

        add_action('comment_form_defaults', function ($x) {
            $user          = wp_get_current_user();
            $user_identity = $user->exists() ? $user->display_name : '';

            $required_text = ' ' . wp_required_field_message();

            $x['logged_in_as'] = sprintf(
                '<p class="logged-in-as">%s%s</p>',
                sprintf(
                /* translators: 1: User name, 2: Edit user link, 3: Logout URL. */
                    __('Logged in as %1$s. <a href="%3$s">Log out?</a>'),
                    $user_identity,
                    get_edit_user_link(),
                    /** This filter is documented in wp-includes/link-template.php */
                    wp_logout_url()
                ),
                $required_text
            );

            return $x;
        });

        // hook before saving a comment to process the comment author name
        add_action('preprocess_comment', function ($commentdata) {
            if (
                str($commentdata['comment_author'])->startsWith('erd')
            ) {

                try {
                    $add = Address::fromBech32($commentdata['comment_author']);
                } catch (\Exception $e) {
                    return $commentdata;
                }

                $fresh = function () use (&$commentdata) {
                    $api = Multiversx::api();
                    try {
                        $resp = $api->accounts()->getByAddress($commentdata['comment_author']);
                        if ($resp instanceof Account && $resp->username) {
                            $commentdata['comment_author'] = $resp->username;

                            $prev                                   = get_option('web3_address_cache', []);
                            $cached                                 = [];
                            $cached[$commentdata['comment_author']] = $resp;
                            $cached                                 = array_merge($prev, $cached);
                            update_option('web3_address_cache', $cached);
                        }
                    } catch (RequestException|ClientException $e) {
                        //
                    }
                };

                $cache = get_option('web3_address_cache');
                if (false !== $cache) {
                    if (isset($cache[$commentdata['comment_author']])) {
                        $cachedInfo = $cache[$commentdata['comment_author']];
                        if ($cachedInfo instanceof Account) {
                            $commentdata['comment_author'] = $cachedInfo->username;
                        }
                    } else {
                        $fresh();
                    }
                } else {
                    $fresh();
                }
            }
            return $commentdata;
        });


        //        dd(get_stylesheet_directory_uri().'/resources/v1.mp3');


        //        add_filter( 'query_vars', function ( $vars ) {
        //            $vars[] = 'custom_page';
        //            return $vars;
        //        } );
        //
        add_action('init', function () {
            add_rewrite_rule('^livewire/update?', 'index.php', 'top');
        }, 10, 0);
    }

    public function shareViewData(): void
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
                if (has_tag('azi-am-invatat')) :
                    $content .= '<p class="block w-full bg-green-200 p-4">Vezi ce alte lucruri am mai invatat in alte zile urmarind postari similare: <a href="/tag/azi-am-invatat/">toate postarile "azi am invatat"</a>.</p>';
                endif;
            }
            return $content;
        }, 0);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        add_image_size('large-not-cropped', $width = 1000, $height = 498, $crop = false);

        add_filter('image_size_names_choose', function ($sizes) {
            $addsizes = [
                "large-not-cropped" => 'Large (not cropped)',
            ];
            $newsizes = array_merge($sizes, $addsizes);
            return $newsizes;
        });


        $this->app->register(CustomFieldsServiceProvider::class);
        $this->app->register(CustomPostTypesProvider::class);

        /**
         * This filter is needed because when I try to log in to my english blog,
         * the redirect url is the main domain with redirect_to query param set to the english blog,
         * this results in a redirect loop when I can never log in into the english blog
         * without manually changing the domain to be on the right wp-login.php domain.
         * This filter changes the domain to the right one.
         */
        add_filter('login_url', function ($login_url, $redirect) {
            if (!str_contains($login_url, 'redirect_to')) {
                return $login_url;
            }
            $redirect_to_url = urldecode(wp_unslash($redirect));
            $redirect_domain = parse_url($redirect_to_url, PHP_URL_HOST);
            $login_domain    = parse_url($login_url, PHP_URL_HOST);
            if ($login_domain != $redirect_domain) {
                $login_url = str_replace($login_domain, $redirect_domain, $login_url);
            }
            return $login_url;
        }, 10, 2);

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
