<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class Handler extends ExceptionHandler
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Passport requests login directly, without passing through Authenticate middleware.
        if ($exception instanceof \Laravel\Passport\Exceptions\AuthenticationException
            && ! $this->shouldReturnJson($request, $exception)) {
            return redirect()->guest(route('loginweb3'));
        }

        return parent::unauthenticated($request, $exception);
    }

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     */
    public function report(\Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     */
    public function render($request, \Throwable $exception): \Illuminate\Http\Response|JsonResponse|RedirectResponse
    {
        return parent::render($request, $exception);
    }
}
