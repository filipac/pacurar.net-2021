<?php

use App\Http\Controllers\Generic\Home;
use App\Http\Controllers\Generic\Page;
use App\Http\Controllers\Generic\Single;
use App\Http\Controllers\Wp\Archive;
use App\Http\Controllers\Wp\Search;
use Laraish\Support\Facades\WpRoute;

WpRoute::home([Home::class, 'blog']);
WpRoute::page('cauta', [Search::class, 'indexSearch']);
WpRoute::page('my-work', [Archive::class, 'work']);
WpRoute::page('search', [Search::class, 'indexSearch']);
WpRoute::page([Page::class, 'index']);
WpRoute::post('post', [Single::class, 'index']);

//WpRoute::post('course', [Single::class, 'index']);

WpRoute::notFound([\App\Http\HandleNotFound::class, 'handle404']);
WpRoute::addRoute('/_404', [\App\Http\HandleNotFound::class, 'handle404Terminate'], ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']);

WpRoute::autoDiscovery();
