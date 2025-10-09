<?php

namespace App\Listeners;

use App\Services\BitacoraService;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoginListener
{
    protected $bitacoraService;

    /**
     * Create the event listener.
     */
    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        try {
            $usuario = $event->user;
            $request = request();

            $this->bitacoraService->registrarLogin(
                $usuario->id_usuario,
                $request ? $request->ip() : null,
                $request ? $request->userAgent() : null
            );
        } catch (\Exception $e) {
            // Log silencioso para no interrumpir el login
            \Log::error('Error al registrar login en bitácora: ' . $e->getMessage());
        }
    }
}
