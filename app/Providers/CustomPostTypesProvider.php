<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Lesson;
use Corcel\Model\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use LaraWelP\Foundation\Support\Wp\Query\QueryResults;

class CustomPostTypesProvider extends ServiceProvider
{
    public function register()
    {
        Post::registerPostType('course', Course::class);
        Post::registerPostType('lesson', Lesson::class);

        Builder::macro('queriedModels', function () {
            global $wp_query;
            $posts = [];

            if ($wp_query) {
                $ids = array_map(function ($post) {
                    return $post->ID;
                }, (array)$wp_query->posts);
                $models = Post::query()->whereIn('ID', $ids)->get();
                return array_map(function ($id) use ($models) {
                    return $models->firstWhere('ID', $id);
                }, $ids);
            }

            return QueryResults::create($posts, $wp_query);
        });


        add_action('init', function () {
            $this->work();
            $this->courses();
        });

        add_action('admin_menu', function () {
            $this->admin();
        }, 99);

        add_action('metronet_reorder_post_types', function ($items) {
            unset($items['course'], $items['lesson']);
            return $items;
        });

        // disable yoast seo for courses and lessons
        add_filter('wpseo_accessible_post_types', function ($post_types) {
            unset($post_types['course'], $post_types['lesson']);
            return $post_types;
        });
    }

    private function admin()
    {
        // register new admin menu item, to group courses and lessons
        add_menu_page(
            page_title: 'Courses',
            menu_title: 'Courses',
            capability: 'manage_options',
            menu_slug: 'edit.php?post_type=course',
            icon_url: 'dashicons-welcome-learn-more',
            position: 5
        );

        remove_submenu_page('edit.php?post_type=course', 'edit.php?post_type=course');
        remove_submenu_page('edit.php?post_type=course', 'edit.php?post_type=lesson');

        add_submenu_page(
            parent_slug: 'edit.php?post_type=course',
            page_title: 'All Courses',
            menu_title: 'All Courses',
            capability: 'manage_options',
            menu_slug: 'edit.php?post_type=course',
        );

        add_submenu_page(
            parent_slug: 'edit.php?post_type=course',
            page_title: 'Add New Course',
            menu_title: 'Add New Course',
            capability: 'manage_options',
            menu_slug: 'post-new.php?post_type=course',
        );

        add_submenu_page(
            parent_slug: 'edit.php?post_type=course',
            page_title: 'All Lessons',
            menu_title: 'All Lessons',
            capability: 'manage_options',
            menu_slug: 'edit.php?post_type=lesson',
        );

        add_submenu_page(
            parent_slug: 'edit.php?post_type=course',
            page_title: 'Add New Lesson',
            menu_title: 'Add New Lesson',
            capability: 'manage_options',
            menu_slug: 'post-new.php?post_type=lesson',
        );
    }

    private function courses()
    {


        register_extended_post_type('course', [
            'public' => true,
            'show_in_rest' => true,
            'has_archive' => true,
            'show_in_feed' => true,
            'supports' => ['title', 'thumbnail'],
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=course',
        ], [
            'singular' => 'Course',
            'plural' => 'Courses',
            'slug' => 'courses'
        ]);

        //post-new.php?post_type=course

        // lesson

        register_extended_post_type('lesson', [
            'public' => true,
            'show_in_rest' => true,
            'has_archive' => false,
            'supports' => ['title'],
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=course',
        ], [
            'singular' => 'Lesson',
            'plural' => 'Lessons',
            'slug' => 'lessons'
        ]);
    }

    /**
     * @return void
     */
    private function work(): void
    {
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
            'show_in_rest' => true,
            'has_archive' => false,
            'public' => false,
            'show_ui' => true,
            'hierarchical' => false,
        ], [
            # Override the base names used for labels:
            'singular' => 'Technology',
            'plural' => 'Technologies',
        ]);
    }
}
