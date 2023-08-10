<?php
namespace App\Models\Wp\Post;

use Laraish\Support\Wp\Model\Post as BaseModel;

class Course extends BaseModel
{
    public const POST_TYPE = 'course';
    public function __construct($pid = null)
    {
        parent::__construct($pid);

        //
    }
}
