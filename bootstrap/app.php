<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsurePasswordUpdated;
use App\Http\Middleware\EnsureSubscribed;
use App\Http\Middleware\EnsureVerified;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\Queries;
use App\Http\Middleware\ResolveOAuthUser;
use App\Http\Middleware\SetLocaleFromHeader;
use App\Http\Middleware\TrackLastActive;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleCors::class);
        $middleware->append(ForceJsonResponse::class);
        $middleware->append(SetLocaleFromHeader::class);
        $middleware->append(Queries::class);
        $middleware->api(prepend: [
            ThrottleRequests::class.':api',
        ]);
        $middleware->alias([
            'track-active' => TrackLastActive::class,
            'verified' => EnsureVerified::class,
            'password-updated' => EnsurePasswordUpdated::class,
            'admin' => EnsureAdmin::class,
            'subscribed' => EnsureSubscribed::class,
            'oauth-user' => ResolveOAuthUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn () => true);

        $exceptions->renderable(function (AuthorizationException $e) {
            return response()->json([
                'error' => 'forbidden',
                'message' => __('responses.auth.forbidden'),
            ], 403);
        });

        $exceptions->renderable(function (NotFoundHttpException $e) {
            $previous = $e->getPrevious();

            if (! $previous instanceof ModelNotFoundException) {
                return null;
            }

            return response()->json([
                'message' => __('responses.model_not_found', [
                    'model' => Str::snake(class_basename($previous->getModel()), ' '),
                    'id' => implode(', ', $previous->getIds()),
                ]),
            ], 404);
        });

        $exceptions->renderable(function (ThrottleRequestsException $e) {
            return response()->json([
                'message' => __('responses.throttle', ['seconds' => $e->getHeaders()['Retry-After']]),
            ], 429)->withHeaders($e->getHeaders());
        });
    })->create();
