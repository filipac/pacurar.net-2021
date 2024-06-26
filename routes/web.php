<?php

use App\Models\Wp\Post\Post;

Route::name('loginSpotify')->get('loginSpotify', function () {
    return \Socialite::with('spotify')
        ->with(["access_type" => "offline"])
        ->scopes(['user-read-playback-state'])
        ->redirect();
});

Route::get('/callbackspotify', function () {
    try {
        $user = Socialite::driver('spotify')->user();
    } catch (InvalidStateException $e) {
        return redirect()->to('/');
    }
    $token = $user->token;
    $refreshToken = $user->refreshToken;
    $expiresIn = $user->expiresIn;
    update_option('spotify_token', $token);
    update_option('spotify_refresh_token', $refreshToken);
    update_option('spotify_expires', $expiresIn);

    return redirect()->to('/');
});

Route::get('/ogImage/{post}', function ($post) {
    $post = get_post($post);
    if (!$post) {
        abort(404);
    }
    $post = new Post($post->ID);
    // dd($post->ogImageBaseUrl());
    // dd($post->gradient_colors);

    return view('misc.ogImage', compact('post'));
    var_dump($post);
});

Route::view('/selling-my-blog', 'rick');

Route::get('egld', function () {
    return view('exchange', []);
});

Route::get('info', function () {
//    $ffi = \FFI::scope("BLOG");
//    dump($ffi->decode_sft_price('AAAABEVHTEQAAAAAAAAAAAAAAAexorwuxQAA'));
    dump(App::version());
    return phpinfo();
});

Route::get('board-games', function () {
    if (defined('ICL_LANGUAGE_CODE')) {
        $lang = ICL_LANGUAGE_CODE;
    } else {
        $lang = 'en';
    }
    if ($lang != 'en') {
        return redirect()->to('https://pacurar.dev/board-games');
    }
    return view('misc.board-games', []);
});

Route::view('login-web3', 'auth.login-web3')->name('loginweb3');

Route::get('token-image/{identifier}', function ($identifier) {
    $url = sprintf(
        "https://github.com/multiversx/mx-assets/blob/master/tokens/%s/logo.png?raw=true",
        $identifier
    );

    $headers = [
        'Cache-Control' => 'max-age=604800',
        'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 604800),
        'Content-Type' => 'image/png',
    ];

    // check if it has the image cached
    $cachedValue = Cache::get('token-image-' . $identifier);
    if ($cachedValue) {
        return response($cachedValue)->withHeaders($headers);
    }

    $resp = \Illuminate\Support\Facades\Http::get($url);
    if ($resp->notFound()) {
        $resp = \Illuminate\Support\Facades\Http::get('https://github.com/multiversx/mx-assets/blob/master/tokens/USDC-c76f1f/logo.png?raw=true');
    }

    $image = $resp->body();

    Cache::put('token-image-' . $identifier, $image, now()->addDays(7));

    return response($image)->withHeaders($headers);

});

Route::any('/config/entry/review/add/product_id/{product_id}', function ($product_id) {
    return response()->redirectTo('https://www.youtube.com/watch?v=dQw4w9WgXcQ', WP_Http::PERMANENT_REDIRECT);
});

Route::any('/products/{product_id}', function ($product_id) {
    return response()->redirectTo('https://www.youtube.com/watch?v=dQw4w9WgXcQ', WP_Http::PERMANENT_REDIRECT);
});

Route::any('/config/{product_id}', function ($product_id) {
    return response()->redirectTo('https://www.youtube.com/watch?v=dQw4w9WgXcQ', WP_Http::PERMANENT_REDIRECT);
})->where('product_id', '.*');
Route::any('/a2f6product/{product_id}', function ($product_id) {
    return response()->redirectTo('https://www.youtube.com/watch?v=dQw4w9WgXcQ', WP_Http::PERMANENT_REDIRECT);
})->where('product_id', '.*');


//Route::get('me', function() {
//   return view('misc.me');
//});
//
//Route::get('user', function () {
////    Auth::loginUsingId(1, true);
////    session()->put('x', 'y');
//    dump(
//        \Illuminate\Support\Facades\Auth::user(),
//        \Illuminate\Support\Facades\Auth::guard('wordpress'),
//        session()->all(),
//        session()->getId(),
//    );
////    dd(\Illuminate\Support\Facades\Auth::guard('wordpress')->user(), \Illuminate\Support\Facades\Session::all(), $_SESSION);
//});


// Route::any('front_page', 'Generic\Home@index');

// Route::any('front_page', 'Generic\Home@blog');
// Route::any('page', 'Generic\Page@index');
// Route::any('single', 'Generic\Single@index');
// Route::any('404', 'Generic\NotFound@index');

// Route::get('my-work', [Archive::class, 'work']);
