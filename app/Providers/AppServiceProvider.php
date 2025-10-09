<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar event listeners para auditoría
        $this->registerAuditoriaEvents();
    }

    /**
     * Registrar eventos de auditoría
     */
    private function registerAuditoriaEvents(): void
    {
        // Eventos de autenticación
        \Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\LoginListener::class
        );

        \Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            \App\Listeners\LogoutListener::class
        );

        // Registrar observers para auditoría de modelos
        \App\Models\User::observe(\App\Observers\BitacoraObserver::class);
        \App\Models\Cliente::observe(\App\Observers\BitacoraObserver::class);
        \App\Models\Rol::observe(\App\Observers\BitacoraObserver::class);
        
        // Verificar si existe el modelo Pedido
        if (class_exists(\App\Models\Pedido::class)) {
            \App\Models\Pedido::observe(\App\Observers\BitacoraObserver::class);
        }
    }
}
