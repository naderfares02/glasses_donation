<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
        'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        'maintenance' => \App\Http\Middleware\MaintenanceMode::class,
        'registration' => \App\Http\Middleware\EnsureRegistrationEnabled::class,
        'phone.verified' => \App\Http\Middleware\EnsurePhoneVerified::class,
    ]);

    $middleware->appendToGroup('web', \App\Http\Middleware\MaintenanceMode::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
