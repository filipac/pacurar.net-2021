<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;
use App\Models\Wp\Post\Post;
use Laraish\Support\Wp\Query\QueryResults;

class Home extends Controller
{
    public function index()
    {
        $data = [
            'version' => app()->version(),
        ];

        return \View::make('generic.home', $data);
    }

    public function blog()
    {
        $data = [
            'posts' => $this->postsWithRandomInsert()
        ];
        return \View::make('generic.blog', $data);
    }
}
