<?php

namespace App\Http\Middleware;

use App\GraphQL\Mutations\VerifyLogin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginFromWalletRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $resp = $next($request);

        if (request()->get('address') && request()->get('signature')) {
            ray(request()->get('address'), request()->get('signature'));

            (new VerifyLogin())(null, [
                'address' => request()->get('address'),
                'signature' => request()->get('signature'),
            ]);

            $has = \Session::has('temp_token');
            if ($has) {
                $r = $resp->withCookie(
                    cookie(name: 'blog_token', value: \Session::get('temp_token'), httpOnly: false)
                );
                auth('wordpress')->user();
                \Session::forget('temp_token');
                \Session::forget('url.intended');
                return $r;
            }

        }

        return $resp;
    }
}
