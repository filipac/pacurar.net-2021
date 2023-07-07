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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $resp = $next($request);

        $resp->headers->add([
            'Content-Type' => 'application/json',
            'Gogo' => 'gaga',
        ]);

        if(str($request->getContent())->contains('verifyLogin')) {
            $data = json_decode($resp->getContent(), true);
            if($data['data']['verifyLogin']['success']) {
                $has = \Session::has('temp_token');
                if($has) {
                    $r = $resp->cookie('blog_token', \Session::get('temp_token'));
                    \Session::forget('temp_token');
                    return $r;
                }
            }
        } else if(str($request->getContent())->contains('logout')) {
            return $resp->cookie('blog_token', null);
        }

        return $resp;
    }
}
