<?php

use App\Http\Controllers\Generic\Home;
use App\Http\Controllers\Generic\Page;
use App\Http\Controllers\Generic\Single;
use App\Http\Controllers\Wp\Search;
use Laraish\Support\Facades\WpRoute;

WpRoute::home([Home::class, 'blog']);
WpRoute::page('cauta', [Search::class, 'indexSearch']);
WpRoute::page('search', [Search::class, 'indexSearch']);
WpRoute::page([Page::class, 'index']);
WpRoute::post('post', [Single::class, 'index']);
WpRoute::autoDiscovery();
