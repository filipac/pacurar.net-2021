<?php
namespace App\Models\Wp\Post;

use LaraWelP\Foundation\Support\Wp\Model\Post as BaseModel;

class Lesson extends BaseModel
{
    public const POST_TYPE = 'lesson';
    public function __construct($pid = null)
    {
        parent::__construct($pid);

        //
    }

    public function course()
    {
        $course = $this->getAttributeFromAcfFields('course');

        return new Course($course);
    }
}
