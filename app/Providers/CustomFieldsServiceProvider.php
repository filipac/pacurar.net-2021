<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use StoutLogic\AcfBuilder\FieldsBuilder;

class CustomFieldsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_action('acf/init', function () {
            acf_add_local_field_group($this->getWork()->build());
            acf_add_local_field_group($this->getMenuItems()->build());

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
        });
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
