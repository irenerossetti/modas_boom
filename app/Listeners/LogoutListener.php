<?php

namespace App\Listeners;

use App\Services\BitacoraService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogoutListener
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
    public function handle(Logout $event): void
    {
        try {
            $usuario = $event->user;
            $request = request();

            if ($usuario) {
                $this->bitacoraService->registrarLogout(
                    $usuario->id_usuario,
                    $request ? $request->ip() : null,
                    $request ? $request->userAgent() : null
                );
            }
        } catch (\Exception $e) {
            // Log silencioso para no interrumpir el logout
            \Log::error('Error al registrar logout en bitácora: ' . $e->getMessage());
        }
    }
}
