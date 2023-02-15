<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LoginWordpressUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (is_user_logged_in() && !\Illuminate\Support\Facades\Auth::guard('wordpress')->check()) {
            $user = wp_get_current_user();
            \Illuminate\Support\Facades\Auth::guard('wordpress')->loginUsingId($user->ID, true);
        } else if(!is_user_logged_in() && \Illuminate\Support\Facades\Auth::guard('wordpress')->check()) {
            \Illuminate\Support\Facades\Auth::guard('wordpress')->logout();
        }
        return $next($request);
    }
}
