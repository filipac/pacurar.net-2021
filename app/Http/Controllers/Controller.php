<?php

namespace App\Http\Controllers;

use App\Models\Wp\Post\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Laraish\Routing\Traits\ViewDebugger as TraitsViewDebugger;
use Laraish\Routing\Traits\ViewResolver;
use Laraish\Routing\WpRouteActionResolver;
use Laraish\Support\Wp\Query\QueryResults;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, TraitsViewDebugger, ViewResolver;

    protected function postsWithRandomInsert()
    {
        if (isset(WpRouteActionResolver::$viewData['posts'])) {
            $posts = WpRouteActionResolver::$viewData['posts'];
        } else {
            $posts = Post::queriedPosts();
        }

        if(isset(WpRouteActionResolver::$viewData['models'])) {
            $models = WpRouteActionResolver::$viewData['models'];
        } else {
            $models = \Corcel\Model\Post::queriedModels();
        }

        if ($posts->count() > 1) {
            $rand = rand(0, $posts->count() - 1);
            $new = [];
            $newModels = [];
            foreach ($posts as $idx => $post) {
                if ($idx == $rand) {
                    $new[] = (object)[
                        'ID' => 'rand',
                    ];
                    $newModels[] = (object)[
                        'ID' => 'rand',
                    ];
                }
                $new[] = $post;
                $newModels[] = $models[$idx];
            }

            $posts = QueryResults::create($new, $posts->wpQuery());
            $models = QueryResults::create($newModels, $posts->wpQuery());
        }
        return [$posts, $models];
    }
}
