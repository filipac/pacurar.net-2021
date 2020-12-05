<?php

namespace App\Http\Controllers\Wp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Archive extends Controller
{
    public function index()
    {
        $data = [
            'posts' => $this->postsWithRandomInsert()
        ];

        // Let Laraish figure out the view file.
        // 'wp.page' is the default view if no matched view found.
        return $this->resolveView('generic.archive', $data);
    }
}
