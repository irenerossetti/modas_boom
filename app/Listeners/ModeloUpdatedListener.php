<?php

namespace App\Listeners;

use App\Services\BitacoraService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ModeloUpdatedListener
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

            // Obtener datos anteriores y nuevos
            $datosAnteriores = $modelo->getOriginal();
            $datosNuevos = $modelo->getAttributes();

            // Solo registrar si hubo cambios reales
            $cambios = $modelo->getDirty();
            if (empty($cambios)) {
                return;
            }

            // Sanitizar datos sensibles
            $datosAnteriores = $this->sanitizarDatos($datosAnteriores);
            $datosNuevos = $this->sanitizarDatos($datosNuevos);

            // Solo incluir los campos que cambiaron
            $datosAnterioresFiltrados = array_intersect_key($datosAnteriores, $cambios);
            $datosNuevosFiltrados = array_intersect_key($datosNuevos, $cambios);

            $this->bitacoraService->registrarActualizacion(
                $nombreModelo,
                $datosAnterioresFiltrados,
                $datosNuevosFiltrados
            );
        } catch (\Exception $e) {
            // Log silencioso para no interrumpir la operación
            \Log::error('Error al registrar actualización en bitácora: ' . $e->getMessage());
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
