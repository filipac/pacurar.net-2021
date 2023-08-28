<?php

namespace App\Http\Controllers\Wp;

use App\Http\Controllers\Controller;
use App\Models\Wp\Post\Post;
use Corcel\Model;
use Illuminate\Http\Request;

class Archive extends Controller
{
    public function index()
    {
        $data = $this->postsWithRandomInsert();
        $data = [
            'posts' => $data[0],
            'models' => $data[1],
        ];

        // Let LaraWelP figure out the view file.
        // 'wp.page' is the default view if no matched view found.
        return $this->resolveView('generic.archive', $data);
    }

    public function work()
    {
        return $this->resolveView('wp.post-archive.work');
    }
}
