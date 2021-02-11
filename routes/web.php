<?php

use App\Http\Controllers\Wp\Archive;
use App\Models\Wp\Post\Post;

Route::name('login')->get('loginSpotify', function () {
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


// Route::any('front_page', 'Generic\Home@index');

// Route::any('front_page', 'Generic\Home@blog');
// Route::any('page', 'Generic\Page@index');
// Route::any('single', 'Generic\Single@index');
// Route::any('404', 'Generic\NotFound@index');

Route::get('my-work', [Archive::class, 'work']);
