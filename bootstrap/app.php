<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\HasPermission;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin'          => AdminMiddleware::class, // Custom admin authentication middleware
            'has_permission' => HasPermission::class, // Custom permission middleware
            'guest'          => RedirectIfAuthenticated::class, // Laravel's built-in guest middleware
            'kyc' => \App\Http\Middleware\RequireKyc::class, // Custom KYC middleware
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));

        // Sanctum token handling for API
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON for unauthenticated API requests
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated. Please provide a valid token.',
                ], 401);
            }
        });

        // Return JSON for authorization failures on API
        $exceptions->render(function (
            \Illuminate\Auth\Access\AuthorizationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'You are not authorized to perform this action.',
                ], 403);
            }
        });
    })
    ->create();
