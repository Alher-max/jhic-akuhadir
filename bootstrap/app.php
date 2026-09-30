<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->throttleApi();

        $middleware->alias([
            'tenant.onboarding' => \App\Http\Middleware\TenantOnboardingMiddleware::class,
            'otp.verified' => \App\Http\Middleware\EnsureOtpIsVerified::class,
            'tenant.subdomain' => \App\Http\Middleware\ResolveTenantSubdomain::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChangeMiddleware::class,
        ]);
        
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SetTenantTimezone::class,
            \App\Http\Middleware\ForcePasswordChangeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                \Illuminate\Support\Facades\Log::error('API Database Query Error: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan pada basis data server.',
                ], 500);
            }
        });

        $exceptions->render(function (\PDOException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                \Illuminate\Support\Facades\Log::error('API Database Connection Error: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan koneksi basis data.',
                ], 500);
            }
        });
    })->create();
