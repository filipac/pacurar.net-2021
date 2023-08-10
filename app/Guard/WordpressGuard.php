<?php

namespace App\Guard;

use Illuminate\Auth\RequestGuard;
use Illuminate\Auth\SessionGuard;

class WordpressGuard extends RequestGuard
{
    public function logout()
    {
        \Cookie::forget('blog_token');
    }

    public function loginUsingId($id, $remember = false)
    {
        $user = \App\Models\WordpressUser::find($id);
        if ($user) {
            $this->setUser($user);
            return true;
        }
        return false;
    }
}
