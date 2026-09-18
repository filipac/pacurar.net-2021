<?php

namespace App\Guard;

use App\Models\WordpressUser;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Cookie;

/** WordPress owns persistent authentication; Laravel does not write remember tokens. */
class WordpressGuard extends RequestGuard implements StatefulGuard
{
    public string $name = 'wordpress';

    private bool $loggedOut = false;

    public function user()
    {
        return $this->loggedOut ? null : parent::user();
    }

    public function validate(array $credentials = [])
    {
        // Preserve RequestGuard's explicit request-validation entry point.
        if (isset($credentials['request'])) {
            return parent::validate($credentials);
        }

        return $this->credentialUser($credentials) !== null;
    }

    public function attempt(array $credentials = [], $remember = false)
    {
        if (! $user = $this->credentialUser($credentials)) {
            return false;
        }
        $this->login($user, $remember);

        return true;
    }

    public function once(array $credentials = [])
    {
        if (! $user = $this->credentialUser($credentials)) {
            return false;
        }
        $this->setUser($user);

        return true;
    }

    public function login(Authenticatable $user, $remember = false)
    {
        $wordpressUser = wp_set_current_user($user->getAuthIdentifier());
        wp_set_auth_cookie($user->getAuthIdentifier(), $remember, is_ssl());
        if ($this->request->hasSession()) {
            $this->request->session()->migrate(true);
        }
        $this->setUser($user);
        do_action('wp_login', $wordpressUser->user_login, $wordpressUser);
    }

    public function logout()
    {
        wp_logout();
        Cookie::queue(Cookie::forget('blog_token'));
        $this->request->cookies->remove('blog_token');
        if ($this->request->hasSession()) {
            $this->request->session()->forget('temp_token');
        }
        $this->forgetUser();
        // Prevent the wallet callback from reauthenticating this same request.
        $this->loggedOut = true;
    }

    public function loginUsingId($id, $remember = false)
    {
        $user = $this->provider->retrieveById($id);
        if ($user) {
            $this->login($user, $remember);

            return $user;
        }

        return false;
    }

    public function onceUsingId($id)
    {
        $user = $this->provider->retrieveById($id);
        if ($user) {
            $this->setUser($user);
        }

        return $user ?: false;
    }

    public function viaRemember()
    {
        // WordPress auth cookies are not Laravel recaller/remember-token cookies.
        return false;
    }

    public function setUser(Authenticatable $user)
    {
        $this->loggedOut = false;

        return parent::setUser($user);
    }

    public function loginUsingWordpressUser(?\WP_User $user)
    {
        if ($user && $user->ID) {
            $model = new WordpressUser((array) $user->data);
            $model->ID = $user->ID;
            $this->setUser($model);

            return true;
        }

        return false;
    }

    private function credentialUser(array $credentials): ?Authenticatable
    {
        if (! is_string($credentials['password'] ?? $credentials['user_pass'] ?? null)) {
            return null;
        }
        $user = $this->provider->retrieveByCredentials($credentials);

        return $user && $this->provider->validateCredentials($user, $credentials) ? $user : null;
    }
}
