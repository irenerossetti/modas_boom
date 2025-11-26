<?php

namespace App\Http\Controllers;

use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BitacoraController extends Controller
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    /**
     * Mostrar la lista de registros de bitácora
     */
    public function index(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (!Auth::check() || !Auth::user()->rol || Auth::user()->rol->nombre !== 'Administrador') {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
        // Validar filtros
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'id_usuario' => 'nullable|integer|exists:usuario,id_usuario',
            'accion' => 'nullable|string|in:LOGIN,LOGOUT,CREATE,UPDATE,DELETE,VIEW',
            'modulo' => 'nullable|string|in:AUTH,USUARIOS,CLIENTES,ROLES,PEDIDOS,BITACORA',
            'busqueda' => 'nullable|string|max:255',
        ]);

        // Obtener filtros del request
        $filtros = $request->only(['fecha_desde', 'fecha_hasta', 'id_usuario', 'accion', 'modulo', 'busqueda']);
        
        // Remover filtros vacíos
        $filtros = array_filter($filtros, function($value) {
            return !is_null($value) && $value !== '';
        });

        // Obtener registros filtrados
        $registros = $this->bitacoraService->obtenerRegistrosFiltrados($filtros, 20);
        
        // Obtener datos para los filtros
        $usuarios = $this->bitacoraService->obtenerUsuariosParaFiltros();
        $acciones = $this->bitacoraService->obtenerAccionesDisponibles();
        $modulos = $this->bitacoraService->obtenerModulosDisponibles();

        // Registrar acceso a la bitácora
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'BITACORA',
            'Usuario accedió a la bitácora del sistema'
        );

        return view('bitacora.index', compact('registros', 'usuarios', 'acciones', 'modulos', 'filtros'));
    }

    /**
     * Limpiar filtros y mostrar todos los registros
     */
    public function limpiarFiltros()
    {
        // Verificar que el usuario sea administrador
        if (!Auth::check() || !Auth::user()->rol || Auth::user()->rol->nombre !== 'Administrador') {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
        
        return redirect()->route('bitacora.index');
    }

    /**
     * Exportar registros de bitácora (funcionalidad futura)
     */
    public function exportar(Request $request)
    {
        // If exports noauth is enabled (local only), note it and bypass admin checks.
        if (config('exports.noauth_enabled', false) === true && app()->environment('local')) {
            \Illuminate\Support\Facades\Log::warning('BitacoraController::exportar - EXPORT_NOAUTH_ENABLED: allowing request without authentication (local only).');
        } else {
            // Verificar que el usuario sea administrador
            if (!Auth::check() || !Auth::user()->rol || Auth::user()->rol->nombre !== 'Administrador') {
                abort(403, 'No tienes permisos para acceder a esta sección.');
            }
        }
        // Validar filtros
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'id_usuario' => 'nullable|integer|exists:usuario,id_usuario',
            'accion' => 'nullable|string|in:LOGIN,LOGOUT,CREATE,UPDATE,DELETE,VIEW',
            'modulo' => 'nullable|string|in:AUTH,USUARIOS,CLIENTES,ROLES,PEDIDOS,BITACORA',
        ]);

        // Registrar intento de exportación
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'BITACORA',
            'Usuario intentó exportar registros de bitácora'
        );

        // Por ahora retornar mensaje de funcionalidad no disponible
        return redirect()->route('bitacora.index')
            ->with('info', 'La funcionalidad de exportación estará disponible próximamente.');
    }
}
