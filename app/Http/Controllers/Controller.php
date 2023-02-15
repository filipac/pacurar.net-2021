<?php

namespace App\Http\Controllers;

use App\Models\Wp\Post\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Laraish\Routing\Traits\ViewDebugger as TraitsViewDebugger;
use Laraish\Routing\Traits\ViewResolver;
use Laraish\Support\Wp\Query\QueryResults;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, TraitsViewDebugger, ViewResolver;

    protected function postsWithRandomInsert()
    {
        $posts = Post::queriedPosts();
        if ($posts->count() > 1) {
            $rand = rand(0, $posts->count() - 1);
            $new = [];
            foreach ($posts as $idx => $post) {
                if ($idx == $rand) {
                    $new[] = (object) [
                        'ID' => 'rand',
                    ];
                }
                $new[] = $post;
            }

            $posts = QueryResults::create($new, $posts->wpQuery());
        }
        return $posts;
    }
}
