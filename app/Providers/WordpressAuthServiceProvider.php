<?php

namespace App\Providers;

use App\Auth\EloquentWordpressUserProvider;
use App\Guard\WordpressGuard;
use App\Hashing\WordPressHasher;
use App\Jwt;
use App\Models\WordpressUser;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class WordpressAuthServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        // on login, check if the user trying to login has a meta "wallet" and disallow login if so
        add_action('wp_login', function ($user_login, $user) {
            $wallet = get_user_meta($user->ID, 'wallet', true);
            $allow_web3_login = apply_filters('allow_web3_login', false);
            if ($wallet && !$allow_web3_login) {
                wp_logout();
                wp_redirect('/wp-login.php?loggedoutweb3=true');
                exit;
            }
        }, 10, 2);

        // if has loggedoutweb3 on wp-login, show a message
        add_action('login_message', function () {
            if (isset($_GET['loggedoutweb3'])) {
                echo '<div class="alert alert-danger alert-dismissible"><p>Web3 login is disabled.</p></div>';
            }
        });

//        add_action('template_redirect', function() {
//            ray(auth()->user());
//        });
    }

    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        Auth::extend('wordpress', function ($app, $name, array $config) {
            return new WordpressGuard(
                callback: function (Request $request, UserProvider $provider) {
                    $cookie = $request->cookie('blog_token');
                    if (!$cookie) {
                        $cookie = \Session::has('temp_token') ? \Session::get('temp_token') : null;
                    }
                    if ($cookie) {
                        $wallet = app('current_wallet');
                        if ($wallet) {
                            $users = get_users([
                                'meta_key' => 'wallet',
                                'meta_value' => $wallet,
                            ]);
                            if (count($users) > 0) {
                                $user = WordpressUser::find($users[0]->ID);
                                if ($user) {
                                    if ($user->ID !== wp_get_current_user()) {
                                        wp_set_auth_cookie($user->ID, 1, is_ssl());
                                        wp_set_current_user($user->ID, $user->user_login);
                                    }
//                                    do_action( 'wp_login', $user->user_login, $user );
                                    return $user;
                                }
                            } else {
                                // create on the fly
                                $user = new WordpressUser();
                                $user->user_login = str()->random(32);
                                $user->user_pass = str()->random(32);
                                $user->user_nicename = str()->random(32);
                                $user->user_email = '';
                                $user->user_url = '';
                                $user->user_registered = now();
                                $user->user_activation_key = '';
                                $user->user_status = 0;
                                $user->display_name = $wallet;

                                $user->save();

                                add_user_meta($user->ID, 'wallet', $wallet);
                                add_user_meta($user->ID, 'created_by_web3', true);

                                wp_set_auth_cookie($user->ID, 1, is_ssl());

                                wp_set_current_user($user->ID, $user->user_login);

                                do_action('wp_login', $user->user_login, $user);

                                return $user;
                            }
                        }
                    }
                    return null;
                },
                request: $app['request'],
                provider: new EloquentWordpressUserProvider($app['wordpress-auth'], $config['model'])
            );
        });

        $this->app->singleton('wordpress-auth', function ($app) {
            $iteration_count = $app['config']->get('wordpress-auth.hash.iteration_count');
            $portable_hashes = $app['config']->get('wordpress-auth.hash.portable_hashes');

            $hasher = new PasswordHash($iteration_count, $portable_hashes);

            return new WordPressHasher($hasher);
        });

        Auth::provider('eloquent.wordpress', function ($app, array $config) {
            return new EloquentWordpressUserProvider($app['wordpress-auth'], $config['model']);
        });

        $this->app->bindIf('current_wallet', function () {
            try {
                return Jwt::getRequiredWallet();
            } catch (\Throwable $th) {
                return null;
            }
        });
    }
}
