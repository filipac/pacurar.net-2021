<?php

namespace Laraish\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laraish\Foundation\Support\Providers\RouteServiceProvider;
use Laravel\Folio\FolioManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WpRouteController extends Controller
{
    public function dispatch(Request $request)
    {
        /** @var WpRouter $wpRouter */
        $wpRouter = app('wpRouter');

        $wpMiddleware = RouteServiceProvider::getWpMiddleware();
        if (empty($wpMiddleware)) {
            require $this->wpRoutes();
        } else {
            $wpRouter->middleware($wpMiddleware)->group($this->wpRoutes());
        }

        try {
            $response = $wpRouter->dispatch($request);
            if ($response->getStatusCode() !== 404) {
                return $response;
            }
        } catch (NotFoundHttpException $e) {
//            dd($e);

        }

        \Event::dispatch('register.folio');
        $manager = app(FolioManager::class);
        $folioHandler = (fn() => $this->handler())->call($manager);
        try {
            $response = $folioHandler($request);

            if ($response instanceof \Illuminate\Http\Response) {
                return $response;
            }
        } catch (NotFoundHttpException $e) {
//            dd(app('wpRouter')->getRouter()->getRoutes());
            $route = app('wpRouter')->getRouter()->getRoutes()->getByAction('App\Http\HandleNotFound@handle404Terminate');
            $route->bind($request);
            $route->run();
        }
    }

    protected function wpRoutes(): string
    {
        return base_path('routes/wp.php');
    }
}
