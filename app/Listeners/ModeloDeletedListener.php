<?php

namespace App\Listeners;

use App\Services\BitacoraService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ModeloDeletedListener
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
    public function handle(object $event): void
    {
        try {
            if (!isset($event->model) || !($event->model instanceof Model)) {
                return;
            }

            $modelo = $event->model;
            $nombreModelo = class_basename($modelo);

            // Solo registrar para modelos específicos
            if (!$this->debeRegistrarModelo($nombreModelo)) {
                return;
            }

            // Obtener datos del modelo eliminado
            $datosEliminados = $modelo->getOriginal() ?: $modelo->getAttributes();
            
            // Sanitizar datos sensibles
            $datosEliminados = $this->sanitizarDatos($datosEliminados);

            $this->bitacoraService->registrarEliminacion(
                $nombreModelo,
                $datosEliminados
            );
        } catch (\Exception $e) {
            // Log silencioso para no interrumpir la operación
            \Log::error('Error al registrar eliminación en bitácora: ' . $e->getMessage());
        }
    }

    /**
     * Determinar si se debe registrar el modelo
     */
    private function debeRegistrarModelo(string $nombreModelo): bool
    {
        $modelosARegistrar = ['User', 'Cliente', 'Rol', 'Pedido'];
        return in_array($nombreModelo, $modelosARegistrar);
    }

    /**
     * Sanitizar datos sensibles
     */
    private function sanitizarDatos(array $datos): array
    {
        $camposSensibles = ['password', 'remember_token', 'api_token'];
        
        foreach ($camposSensibles as $campo) {
            unset($datos[$campo]);
        }

        return $datos;
    }
}
