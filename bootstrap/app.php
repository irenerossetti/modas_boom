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
        $middleware->trustProxies(at: '*');

        // Excluir rutas de autenticación del CSRF temporalmente
        $middleware->validateCsrfTokens(except: [
            'login',
            'register',
            'password/*',
        ]);

        $middleware->alias([
            'login.throttle' => \App\Http\Middleware\LoginAttemptThrottle::class,
            'user.enabled' => \App\Http\Middleware\CheckUserEnabled::class,
            'admin.role' => \App\Http\Middleware\CheckAdminRole::class,
            'auditoria' => \App\Http\Middleware\AuditoriaMiddleware::class,
            'redirect.role' => \App\Http\Middleware\RedirectByRole::class,
            'vendedor.role' => \App\Http\Middleware\VendedorRoleMiddleware::class,
            'admin.cliente.role' => \App\Http\Middleware\AdminOrClienteRole::class,
            'role' => \App\Http\Middleware\CheckMultipleRoles::class,
        ]);
        
        // Aplicar middleware de auditoría a rutas web autenticadas
        $middleware->web(append: [
            \App\Http\Middleware\AuditoriaMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
