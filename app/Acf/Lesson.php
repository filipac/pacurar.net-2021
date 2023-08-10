<?php

namespace App\Acf;

use StoutLogic\AcfBuilder\FieldsBuilder;

class Lesson
{
    public function __invoke()
    {
        $work = new FieldsBuilder('lesson');
        $work
            ->addPostObject('course', [
                'label' => 'Course',
                'post_type' => ['course'],
                'filters' => [
                    0 => 'search',
                    1 => 'post_type',
                ],
                'return_format' => 'id',
                'required' => 1,
                'max' => 1,
            ])
            ->setLocation('post_type', '==', 'lesson');

        acf_add_local_field_group($work->build());
    }
}
