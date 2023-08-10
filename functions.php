<?php

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Str;

remove_filter('template_redirect', 'redirect_canonical');

global $wp_filesystem;
if ($wp_filesystem === null) {
    require_once(ABSPATH . '/wp-admin/includes/file.php');
    WP_Filesystem();
}
if (!defined('ICL_LANGUAGE_CODE')) {
    define('ICL_LANGUAGE_CODE', 'ro');
}
$classRequired = $wp_filesystem->wp_plugins_dir() . '/semantic-linkbacks/includes/class-linkbacks-avatar-handler.php';
if (file_exists($classRequired)) {
    require_once $classRequired;
}

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


// add_action('sensei_before_main_content', function () {
//     $html = view('generic.sensei')->render();
//     echo Str::before($html, '<!--content here!-->');
// }, 10);
// add_action('sensei_after_main_content', function () {
//     $html = view('generic.sensei')->render();
//     echo Str::after($html, '<!--content here!-->');
// }, 10);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$kernel->handle($request = Illuminate\Http\Request::capture());

add_action('wp_headers', function ($headers) {
    global $wp;
    if ($wp->request == 'gql') {
        $headers['Content-Type'] = 'application/json';
    } else if($wp->request == '_debugbar/assets/javascript') {
        $headers['Content-Type'] = 'application/javascript';
    } else if(str($wp->request)->contains('public/assets') && str($wp->request)->endsWith('.js')) {
        $headers['Content-Type'] = 'application/javascript';
    } else if($wp->request == '_debugbar/assets/stylesheets') {
        $headers['Content-Type'] = 'text/css';
    } else if(str($wp->request)->contains('public/assets') && str($wp->request)->endsWith('.css')) {
        $headers['Content-Type'] = 'text/css';
    }
    return $headers;
});

$app['events']->listen(RequestHandled::class, function (RequestHandled $event) use ($kernel) {
    if ($event->response->getStatusCode() === 404 || $event->request->fullUrlIs('*.js')) {
        return;
    }

    if ($event->request->getPathInfo() == '/_debugbar/assets/javascript') {
        $event->response->header('Content-Type', 'application/javascript');
    }

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
    $url = get_comment_author_url($comment);
    $author = get_comment_author($comment);

    if (empty($url) || 'http://' === $url) {
        $return = $author;
    } else {
        $return = "<a target=\"_blank\" href='$url' rel='external nofollow ugc' class='url'>$author</a>";
    }

    /**
     * Filters the comment author's link for display.
     *
     * @param string $return The HTML-formatted comment author link.
     *                           Empty for an invalid URL.
     * @param string $author The comment author's username.
     * @param int $comment_ID The comment ID.
     *
     * @since 4.1.0 The `$author` and `$comment_ID` parameters were added.
     *
     * @since 1.5.0
     */
    return apply_filters('get_comment_author_link', $return, $author, $comment->comment_ID);
}
