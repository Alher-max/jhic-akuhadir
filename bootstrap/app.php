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
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
