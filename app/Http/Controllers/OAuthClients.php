<?php

namespace App\Http\Controllers;

use App\Auth\OAuthClientManager;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

final class OAuthClients extends Controller
{
    public function index(Request $request)
    {
        $query = Passport::client()->newQuery();
        $total = $query->count();
        $pages = max(1, (int) ceil($total / 20));
        $page = min($pages, max(1, (int) $request->query('page', 1)));
        $clients = $query->orderByDesc('created_at')->orderBy('id')->skip(($page - 1) * 20)->take(20)->get();

        return view('oauth.clients', compact('clients', 'total', 'pages', 'page'));
    }

    public function mutate(Request $request, OAuthClientManager $manager)
    {
        return response()->json($manager->mutate($request->all()));
    }
}
