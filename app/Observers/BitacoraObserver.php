<?php

namespace App\Observers;

use App\Services\BitacoraService;
use Illuminate\Database\Eloquent\Model;

class BitacoraObserver
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        try {
            $nombreModelo = class_basename($model);

            // Solo registrar para modelos específicos y evitar recursión
            if (!$this->debeRegistrarModelo($nombreModelo)) {
                return;
            }

            // Obtener datos del modelo creado
            $datosNuevos = $model->getAttributes();
            
            // Sanitizar datos sensibles
            $datosNuevos = $this->sanitizarDatos($datosNuevos);

            $this->bitacoraService->registrarCreacion(
                $nombreModelo,
                $datosNuevos
            );
        } catch (\Exception $e) {
            // Log silencioso para no interrumpir la operación
            \Log::error('Error al registrar creación en bitácora: ' . $e->getMessage());
        }
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        try {
            $nombreModelo = class_basename($model);

            // Solo registrar para modelos específicos y evitar recursión
            if (!$this->debeRegistrarModelo($nombreModelo)) {
                return;
            }

            // Obtener datos anteriores y nuevos
            $datosAnteriores = $model->getOriginal();
            $datosNuevos = $model->getAttributes();

            // Solo registrar si hubo cambios reales
            $cambios = $model->getDirty();
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
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        try {
            $nombreModelo = class_basename($model);

            // Solo registrar para modelos específicos y evitar recursión
            if (!$this->debeRegistrarModelo($nombreModelo)) {
                return;
            }

            // Obtener datos del modelo eliminado
            $datosEliminados = $model->getOriginal() ?: $model->getAttributes();
            
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
        // Evitar recursión con el modelo Bitacora
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
