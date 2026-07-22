<?php

use App\Http\Middleware\TrustHosts;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            // ✅ Core Laravel Middleware (priority first)
            \App\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\TrustHosts::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,

            // ✅ Security
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\ValidateHostHeader::class,

            // ✅ Request Processing
            \App\Http\Middleware\TrimStrings::class,
            \App\Http\Middleware\NormalizeDoubleSlash::class,

            // ✅ Custom Logic
            //\App\Http\Middleware\LoadSettings::class,
            \App\Http\Middleware\Locale::class,
            \App\Http\Middleware\JwtCookieMiddleware::class,

        ]);
        // Route middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
        $middleware->alias([
            'portal.auth' => \App\Http\Middleware\PortalAuthentication::class,
        ]);

    })
->withExceptions(function (Exceptions $exceptions) {
})
    ->create();