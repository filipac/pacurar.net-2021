<?php

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Str;

remove_filter('template_redirect', 'redirect_canonical');

if (defined('ARTISAN_BINARY')) {
    return;
}


define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

require __DIR__ . '/vendor/autoload.php';


/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__ . '/bootstrap/app.php';


/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

add_action(
    'after_setup_theme',
    function () {
        add_theme_support('sensei');
    }
);

// add_action('sensei_before_main_content', function () {
//     $html = view('generic.sensei')->render();
//     echo Str::before($html, '<!--content here!-->');
// }, 10);
// add_action('sensei_after_main_content', function () {
//     $html = view('generic.sensei')->render();
//     echo Str::after($html, '<!--content here!-->');
// }, 10);
add_action(
    'after_setup_theme',
    function () {
        add_theme_support('sensei');
    }
);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$kernel->handle($request = Illuminate\Http\Request::capture());

$app['events']->listen(RequestHandled::class, function (RequestHandled $event) use ($kernel) {
    $event->response->send();

    $kernel->terminate($event->request, $event->response);
});

//global $woothemes_sensei;
//remove_action('sensei_before_main_content', array( $woothemes_sensei->frontend, 'sensei_output_content_wrapper' ), 10);
//remove_action('sensei_after_main_content', array( $woothemes_sensei->frontend, 'sensei_output_content_wrapper_end' ), 10);

// add_filter('sensei_show_main_footer', '__return_false');
// add_filter('sensei_show_main_header', '__return_false');

function get_comment_author_link_blank($comment_ID = 0)
{
    $comment = get_comment($comment_ID);
    $url     = get_comment_author_url($comment);
    $author  = get_comment_author($comment);

    if (empty($url) || 'http://' === $url) {
        $return = $author;
    } else {
        $return = "<a target=\"_blank\" href='$url' rel='external nofollow ugc' class='url'>$author</a>";
    }

    /**
     * Filters the comment author's link for display.
     *
     * @since 1.5.0
     * @since 4.1.0 The `$author` and `$comment_ID` parameters were added.
     *
     * @param string $return     The HTML-formatted comment author link.
     *                           Empty for an invalid URL.
     * @param string $author     The comment author's username.
     * @param int    $comment_ID The comment ID.
     */
    return apply_filters('get_comment_author_link', $return, $author, $comment->comment_ID);
}
