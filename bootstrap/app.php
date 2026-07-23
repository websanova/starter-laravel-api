<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->append(\App\Http\Middleware\ForceJsonResponse::class);
        $middleware->append(\App\Http\Middleware\SetLocaleFromHeader::class);
        $middleware->append(\App\Http\Middleware\Queries::class);
        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
        ]);
        $middleware->alias([
            'track-active' => \App\Http\Middleware\TrackLastActive::class,
            'verified' => \App\Http\Middleware\EnsureVerified::class,
            'password-updated' => \App\Http\Middleware\EnsurePasswordUpdated::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'subscribed' => \App\Http\Middleware\EnsureSubscribed::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (ThrottleRequestsException $e) {
            return response()->json([
                'message' => __('responses.throttle', ['seconds' => $e->getHeaders()['Retry-After']]),
            ], 429)->withHeaders($e->getHeaders());
        });
    })->create();
