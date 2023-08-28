<?php

namespace App\Http\Controllers\Generic;

use App\Http\Controllers\Controller;

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
        $data = $this->postsWithRandomInsert();
        $data = [
            'posts' => $data[0],
            'models' => $data[1],
        ];
        return \View::make('generic.blog', $data);
    }
}
