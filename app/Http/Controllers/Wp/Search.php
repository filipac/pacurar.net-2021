<?php

namespace App\Http\Controllers\Wp;

use App\Http\Controllers\Controller;
use App\Models\Wp\Post\Post;
use Illuminate\Http\Request;

class Search extends Controller
{
    public function indexSearch()
    {
        return $this->resolveView('generic.search_root');
    }
    public function index()
    {
        $data = [
            'posts' => Post::queriedPosts(),//$this->postsWithRandomInsert()
        ];

        // Let Laraish figure out the view file.
        // 'wp.page' is the default view if no matched view found.
        return $this->resolveView('generic.search', $data);
    }
}
