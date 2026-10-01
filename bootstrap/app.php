<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            $response = null;

            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();

                if (in_array($status, [401, 403, 404, 419, 422, 429, 500, 503], true)) {
                    return Inertia::render('Error', [
                        'status' => $status,
                        'message' => $e->getMessage() ?: null,
                    ])->toResponse($request)->setStatusCode($status);
                }
            } elseif (! config('app.debug')) {
                return Inertia::render('Error', [
                    'status' => 500,
                    'message' => null,
                ])->toResponse($request)->setStatusCode(500);
            }

            return null;
        });
    })->create();
