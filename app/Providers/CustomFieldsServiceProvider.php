<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use StoutLogic\AcfBuilder\FieldsBuilder;

class CustomFieldsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $work = new FieldsBuilder('work');
        $work
        ->addText('type')
        ->addUrl('url')
        ->addNumber('colspan')
        ->setLocation('post_type', '==', 'work');

        add_action('acf/init', function () use ($work) {
            acf_add_local_field_group($work->build());
        });
    }
}
