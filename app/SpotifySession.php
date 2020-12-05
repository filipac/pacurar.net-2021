<?php


namespace App;


use SpotifyWebAPI\Session;

class SpotifySession
{
    public static function get()
    {
        $session = new Session(
            config('services.spotify.client_id'),
            config('services.spotify.client_secret'),
            config('services.spotify.redirect'),
        );
        $token = get_option('spotify_token');
        $refreshToken = get_option('spotify_refresh_token');

        if ($token) {
            $session->setAccessToken($token);
            $session->setRefreshToken($refreshToken);
        }

        return $session;
    }
}
