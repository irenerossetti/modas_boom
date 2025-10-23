<?php

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
        $middleware->alias([
            'login.throttle' => \App\Http\Middleware\LoginAttemptThrottle::class,
            'user.enabled' => \App\Http\Middleware\CheckUserEnabled::class,
            'admin.role' => \App\Http\Middleware\CheckAdminRole::class,
            'auditoria' => \App\Http\Middleware\AuditoriaMiddleware::class,
            'redirect.role' => \App\Http\Middleware\RedirectByRole::class,
        ]);
        
        // Aplicar middleware de auditoría a rutas web autenticadas
        $middleware->web(append: [
            \App\Http\Middleware\AuditoriaMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
