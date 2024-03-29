# Laravel in WordPress Theme

Laravel is a web application framework with expressive, elegant syntax. It's one of the most popular PHP frameworks today.

LaraWelP brings the Laravel Framework into WordPress, which allow us to have all the benefits of Laravel. So you can create themes with less effort, more enjoyment!


## Requirement

The 99% of LaraWelP is just the regular full stack PHP Framework [Laravel](https://laravel.com/). So if you have never heard of it, you're going to want to take a look at it before you can go any further.

For those who are already familiar with Laravel, it should be a piece of cake for you to get started with LaraWelP.


## What LaraWelP is and is not

**LaraWelP is not a framework for general purpose WordPress theme development.**

Yes, it is a framework but not for general WordPress theme development. LaraWelP is aimed at helping create "homemade theme" rather than general purpose theme. So if you want to create themes with a bunch of theme options for sales or just for free distribution, you probably want to take a look at the following frameworks instead.

* [Piklist](https://piklist.com/product/piklist/)
* [Gantry](http://gantry.org/)
* [Unyson](http://unyson.io/)


## What's the diffrence between the original Laravel?

I'd say almost no differences there, except some additional tweaking, which gets Laravel to work well inside a WordPress theme. So basically you could do anything that you could do with Laravel, it's just the regular Laravel inside a WordPress theme. If you are curious about what exactly have been modified, taking a diff to the original Laravel would make sense for you.


# Get Started

## Installation

You can install LaraWelP by issuing the following command via [Composer](https://getcomposer.org/).
`composer create-project --prefer-dist larawelp/theme <theme-name>`

Note that **the MySQL server and the web server must be running before you can issue the `composer create-project` command** to install LaraWelP. Because after Composer finishes the installation, it's going to run an artisan command, which requires MySQL server and the web server that host the WordPress be running at the time you issuing the command.

Also, notice that if you are on Mac and use MAMP or similar application to create your local server environment you may need to change your `$PATH` environment variable to make Composer use the PHP binary that MAMP provides rather than the OS's built-in PHP binary.

## Routing
LaraWelP replaced the original `UriValidator`(`Illuminate\Routing\Matching\UriValidator`) with its own one to allow you to specify WordPress specific routes, like "archive" or "page" or "custom post type" ex.

To define a WordPress specific route, just by providing a "page type" as the first argument.

For example:

```php
// The "about" page
Route::any('page.about', Controller@method);

// The child page "works" of "about".
Route::any('page.about.works', Controller@method);

// Any child page of "about".
Route::any('page.about.*', Controller@method);

// Any descendant page of "about".
Route::any('page.about.**', Controller@method);

// Grouping multiple routes that sharing a common `prefix`.
Route::group(['prefix' => 'page'], function () {
    
    Route::any('about.contact', function () {
        return 'Foo'; // equivalent to <page.about.contact>
    });
    
    Route::any('service.*.price', function () {
        return 'Bar'; // equivalent to <page.service.*.price>
    });
    
});


// IMPORTANT !
//
// Routes that has a higher specificity should be 
// placed more above(earlier) than the routes that have a lower specificity.
// Why? If you place the routes that have a lower specificity,
// the subsequent routes that have a higher specificity will be ignored.
//
// The following routes have a lower specificity than the above ones.
// So you want to place them here.

// Generic pages
Route::any('page', Controller@method);

// Front page
Route::any('front_page', Controller@method); 

// Post archive index page
Route::any('archive', Controller@method);

```

Here's some notes you should keep in mind.

* You can use a "dot notation" to specify the hierarchy for pages and taxonomies.
* You can use the wild card to specify any child/descendant page/term of a parent/ancestor page/term.
* You should care about the order of your routes. Routes that has a higher specificity should be placed more above than the routes that have a lower specificity.

What's more, you can even write your own routes by URI, and it just works.

```php
// This will use the original UriValidator of Laravel.
Route::get('/my/endpoint', function () {
    return 'Magic!';
});
```

## Models
LaraWelP comes with some general purpose models like `Post` or `Term` model. Note that they are not an implementation of ORM like the Laravel's Eloquent Model. They are just a simple wrapper for WordPress's APIs that encapsulate some common logic to help you simplify your business logic. 

You can find those models in `LaraWelP\WpSupport\Model`. Because the `Post` model is the most frequently used model, for convenience, a `Post` Class that extends the `LaraWelP\WpSupport\Model\Post` has brought to your `app/Models` directory already.

Let's take a look at an example. 

See you have a route like this :

```php
Route::any('archive', 'Generic\Archive@index');
```

In your controller `app\Http\Controllers\Generic\Archive` :

```php
<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;
use App\Models\Post;

class Archive extends Controller
{
    public function index()
    {
        $data = [
            'posts' => Post::queriedPosts() // get the posts for current page
        ];

        return $this->view('generic.archive', $data);
    }
}
```

In your view `generic.archive` :

```php
<main class="posts">
    @foreach($posts as $post)
        <section class="post">
            <a class="post" href="{{ $post->permalink }}">
                <img class="post__thumbnail" src="{{ $post->thumbnail->url }}" alt="{{ $post->title }}">
            </a>
            <time class="post__time" datetime="{{ $post->dateTime }}">{{ $post->date }}</time>
            <a class="post__category" href="{{ $post->category->url }}">{{ $post->category->name }}</a>
            <h1 class="post__title">{{ $post->title }}</h1>
        </section>
    @endforeach

    {{  $posts->getPagination() }}
</main>
```

As you can see in the example above, you can get common properties of a post, like `$post->permalink` or `$post->title` etc. 

Actually, those `properties` are not "real properties". When you access property like `$post->permalink`, under the hood, it'll call `$post->permalink()` to get the value for you automatically, and from the second time when you access the same property, it won't call `$post->permalink()` again, instead, it'll return the cached value from previous calling result. If you don't want to use cached value, you can call the method explicitly like `$post->title()`. Also, feel free to create your own "properties" by adding public methods to your model class.

Take a look at [LaraWelP\WpSupport\Model](https://github.com/larawelp/framework/tree/master/WpSupport/Model), there are some predefined "properties" that you may want to use. 


## The `@loop` blade directive
LaraWelP also added a `@loop` blade directive for simplifying "[The Loop](https://codex.wordpress.org/The_Loop)" in WordPress.

for example:

```php
@loop($posts as $post)
	{{ get_the_title() }}
@endloop
```

will be compiled to

```php
<?php foreach($posts as $post): setup_the_post( $post->wpPost ); ?>
                
    <?php echo e(get_the_title()); ?>

<?php endforeach; wp_reset_postdata(); ?>
```

where `$post` should be a `Post` model object.

Usually you don't want to use the `@loop` directive. Because it'll introduce some unnecessary overheads. Keep in mind that always prefer `@foreach` to `@loop`. Except you want to access some properties like `content` or `excerpt` which requiring must be retrieved within "The Loop", otherwise never use the `@loop` actively.


## Theme Options
Setup the custom post type, register the navigation menus ... There always are some common tasks you have to deal with when you start to build a WordPress theme. The `app/config/theme.php` is where you define all your common tasks. 

Some basic options are predefined for you. Take a look at the [config/theme.php](https://github.com/larawelp/theme/blob/master/config/theme.php).

Also, you can create your own options by adding new static methods to the `App\Providers\ThemeOptionsProvider`. The name of the method will become to an option.


## Actions and Filters
You define your actions and filters in `App\Providers\EventServiceProvider` just like the laravel's event.

The following example adding a `pre_get_posts` action, and the `handle` method of `App\Listeners\MainQueryListener` will be called for this action.

```php
<?php

namespace App\Providers;

use LaraWelP\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register the WordPress actions
     * @var array
     */
    protected $action = [
            'pre_get_posts' => 'App\Listeners\MainQueryListener'
    ];

    /**
     * Register the WordPress filters
     * @var array
     */
    protected $filter = [];
}
```


## Pagination
You can get the pagination by calling the `getPagination` method of `Post`.

```php
use App\Models\Post;

$posts = Post::queriedPosts();
```

```html
<div>
	{{  $posts->getPagination() }}
</div>
```

By providing additional parameters, you can specify the view file and several options. See [larawelp/pagination](https://github.com/larawelp/pagination) for more details.


## Options page
Perhaps creating options pages is one of the most tedious tasks. 
If you've used the WordPress's API to create options pages, you know how dirty the code is going to be… 

LaraWelP provides a powerful and yet clean API to help you creating the options pages.

See [larawelp/options](https://github.com/larawelp/options) for more details.


## View debugger
Sometimes, you just want to get some basic information about the current view(page) being displayed. For example, the path of the view file, or the name of the controller that was used.

To get the basic information of the current view being displayed, you include the `ViewDebbuger` trait in your `App\Http\Controllers`. Open your console of your browser, and you could find something like this:

```json
{
    "view_path": "/var/www/example/wp-content/themes/example/resources/views/singular/news.blade.php",
    "compiled_path": "/var/www/example/wp-content/themes/example/storage/framework/views/befa3e2a2cb93be21c6ebf30a60824a5d2a2ed11.php",
    "data": {
        "post": {}
    },
    "controller": "App\\Http\\Controllers\\Singular\\News"
}
```

Note that when `APP_ENV=production` is set in your `.env` file, nothing will be outputted to the console.


## Run artisan command
As I mentioned in the [Installation](#installation) section. To run an artisan command, you have to meet the following conditions.

* The MySQL server and the web server must be running.
* If you are on Mac and use MAMP or similar application to create your local server environment you may need to change your `$PATH` environment variable to make Composer use the PHP binary that MAMP provides rather than the OS's built-in PHP binary.


# Known Issue
If you have a plugin using Composer, and that plugin has the same dependency as your theme(LaraWelP) has, may lead to a problem when they are using a different version of that dependency.  In such a situation, it'll `require` multiple Composer Autoloaders(`vendor/autoload.php`), and **the last loaded one will take priority over the previous ones**.

Say you have a plugin that depends on the package `Foo (v1.2.0)`, and your theme depends on the same package `Foo (v2.0.1)`; such a situation may lead to load the unintended version of `Foo`. Which version will be used depend on the time the `autoloader.php` was loaded and the time the package(class) was used.

Being that said, this is not a Composer specific issue. I'd say it's a WordPress issue that needs to be solved somehow.

Here are some articles discussing this issue in WordPress.

* [A Narrative of Using Composer in a WordPress Plugin](https://wptavern.com/a-narrative-of-using-composer-in-a-wordpress-plugin)
