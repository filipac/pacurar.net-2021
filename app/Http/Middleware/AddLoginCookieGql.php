<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddLoginCookieGql
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * @var \Illuminate\Http\Response $resp
         */
        $resp = $next($request);

        if ($request->header('Content-Type') === 'application/json') {
            $resp->headers->add([
                'Content-Type' => 'application/json',
            ]);
        }

        if ($request->method() !== 'POST') {
            return $resp;
        }


        if (str($request->getContent())->contains('verifyLogin')) {
            $data = json_decode($resp->getContent(), true);
            if (isset($data['data']) && $data['data']['verifyLogin']['success']) {
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
        } else if (str($request->getContent())->contains('logout')) {
            \Session::forget('url.intended');
            wp_logout();
            auth()->logout();
            return $resp->withCookie(
                cookie(name: 'blog_token', value: null, httpOnly: false)
            );
        }

        return $resp;
    }
}
