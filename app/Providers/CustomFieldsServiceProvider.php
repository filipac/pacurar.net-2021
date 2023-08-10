<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use StoutLogic\AcfBuilder\FieldsBuilder;

class CustomFieldsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_action('wp_loaded', function () {
            if (!did_action('acf/init')) {
                return;
            }
            acf_add_local_field_group($this->getWork()->build());
            acf_add_local_field_group($this->getMenuItems()->build());

            // get all registered custom post types
            $post_types = get_post_types(['_builtin' => false], 'names', 'and');
            $namespace = 'App\\Acf\\';
            foreach ($post_types as $post_type) {
                $class_name = str($post_type)
                    ->title()
                    ->replace(['_', '-'], '')
                    ->prepend($namespace)
                    ->toString();
                if (class_exists($class_name)) {
                    $instance = app()->make($class_name);
                    if (method_exists($instance, '__invoke')) {
                        // call using the container
                        app()->call($instance);
                    }
                }
            }

            acf_register_block([
                'name' => 'display_menu',
                'title' => __('Display Menu'),
                'description' => __('A custom display menu widget.'),
                'render_callback' => function ($attrs) {
                    $menu_slug = get_field('which_menu');
                    $title = defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'en' ? 'Menu' : 'Meniu';
                    echo view('widgets.menu', compact('menu_slug', 'title', 'attrs'))->render();
                },
                'category' => 'widgets',
                'icon' => 'admin-links',
            ]);

            acf_register_block([
                'name' => 'diversity_visa',
                'title' => __('Diversity visa'),
                'render_callback' => function ($attrs) {
                    $title = defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'en' ? 'Diversity Visa' : 'Loteria vizelor';
                    echo view('widgets.dv', compact('title', 'attrs'))->render();
                },
                'category' => 'widgets',
                'icon' => 'admin-links',
            ]);

            acf_register_block([
                'name' => 'ad_space',
                'title' => __('Ad space'),
                'render_callback' => function ($attrs) {
                    $title = defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'en' ? 'Ad space' : 'Spațiu publicitar';
                    echo view('widgets.ad', compact('title', 'attrs'))->render();
                },
                'category' => 'widgets',
                'icon' => 'admin-links',
            ]);
        }, 999);
    }

    /**
     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
     */
    private function getWork(): FieldsBuilder
    {
        $work = new FieldsBuilder('work');
        $work
            ->addText('type')
            ->addUrl('url')
            ->addNumber('colspan')
            ->setLocation('post_type', '==', 'work');
        return $work;
    }

    /**
     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
     */
    private function getMenuItems(): FieldsBuilder
    {
        // get all menus available and create options array
        $menu_options = [];
        if (is_admin()) {
            $menus = get_terms('nav_menu');
            foreach ($menus as $menu) {
                $menu_options[] = [$menu->slug => $menu->name];
            }
        }
        $acf = new FieldsBuilder('display_menu');
        $acf
            ->addSelect('which_menu')
            ->addChoices($menu_options)
            ->setLocation('block', '==', 'acf/display-menu');
        return $acf;
    }
}
