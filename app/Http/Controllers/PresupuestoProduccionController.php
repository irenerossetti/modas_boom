<?php

namespace App\Http\Controllers;

use App\Models\PresupuestoProduccion;
use App\Models\User;
use App\Models\Pedido;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PresupuestoProduccionController extends Controller
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Verificar permisos (solo empleados y administradores)
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        // Validar filtros
        $request->validate([
            'estado' => 'nullable|string|in:Borrador,Aprobado,Utilizado',
            'tipo_prenda' => 'nullable|string|max:255',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        // Construir consulta con filtros
        $query = PresupuestoProduccion::with(['usuarioRegistro', 'pedido'])
            ->byEstado($request->estado)
            ->byTipoPrenda($request->tipo_prenda);

        // Filtros de fecha
        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $presupuestos = $query->orderBy('created_at', 'desc')->paginate(15);

        // Obtener datos para filtros
        $estados = ['Borrador', 'Aprobado', 'Utilizado'];
        $tiposPrenda = PresupuestoProduccion::select('tipo_prenda')
            ->distinct()
            ->orderBy('tipo_prenda')
            ->pluck('tipo_prenda');

        // Registrar acceso
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'PRESUPUESTOS_PRODUCCION',
            'Usuario accedió a la lista de presupuestos de producción'
        );

        return view('presupuestos-produccion.index', compact(
            'presupuestos', 'estados', 'tiposPrenda'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Verificar permisos
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para crear presupuestos.');
        }

        // Obtener pedido si viene desde un pedido específico
        $pedido = null;
        if ($request->has('pedido_id')) {
            $pedido = Pedido::with('cliente')->findOrFail($request->pedido_id);
        }

        // Obtener tipos de prenda y tela existentes para sugerencias
        $tiposPrenda = PresupuestoProduccion::select('tipo_prenda')
            ->distinct()
            ->orderBy('tipo_prenda')
            ->pluck('tipo_prenda');

        $tiposTela = PresupuestoProduccion::select('tipo_tela')
            ->distinct()
            ->orderBy('tipo_tela')
            ->pluck('tipo_tela');

        return view('presupuestos-produccion.create', compact('pedido', 'tiposPrenda', 'tiposTela'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verificar permisos
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para crear presupuestos.');
        }

        // Validar datos
        $request->validate([
            'tipo_prenda' => 'required|string|max:255',
            'tipo_tela' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'id_pedido' => 'nullable|exists:pedido,id_pedido',
            
            // Costos de materiales
            'costo_tela' => 'required|numeric|min:0',
            'costo_cierre' => 'required|numeric|min:0',
            'costo_boton' => 'required|numeric|min:0',
            'costo_bolsa' => 'required|numeric|min:0',
            'costo_hilo' => 'required|numeric|min:0',
            'costo_etiqueta_cinta' => 'required|numeric|min:0',
            'costo_etiqueta_carton' => 'required|numeric|min:0',
            
            // Costos de mano de obra
            'costo_tallerista' => 'required|numeric|min:0',
            'costo_planchado' => 'required|numeric|min:0',
            'costo_ayudante' => 'required|numeric|min:0',
            'costo_cortador' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, &$presupuesto) {
                // Crear presupuesto
                $presupuesto = new PresupuestoProduccion($request->all());
                $presupuesto->id_usuario_registro = Auth::user()->id_usuario;
                
                // Calcular totales
                $presupuesto->actualizarTotales();
                
                // Validar regla de negocio: costo total > 0
                if ($presupuesto->costo_total <= 0) {
                    throw new \Exception('El costo total del presupuesto debe ser mayor a cero.');
                }
                
                $presupuesto->save();
            });

            // Registrar en bitácora
            $this->bitacoraService->registrarActividad(
                'CREATE',
                'PRESUPUESTOS_PRODUCCION',
                "Se creó el presupuesto de producción #{$presupuesto->id} para {$presupuesto->tipo_prenda} - Total: {$presupuesto->costo_total_formateado}",
                null,
                $presupuesto->toArray()
            );

            return redirect()->route('presupuestos-produccion.show', $presupuesto->id)
                ->with('success', "Presupuesto de producción creado exitosamente. Total: {$presupuesto->costo_total_formateado}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Verificar permisos
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para ver presupuestos.');
        }

        $presupuesto = PresupuestoProduccion::with(['usuarioRegistro', 'pedido.cliente'])
            ->findOrFail($id);

        // Registrar visualización
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'PRESUPUESTOS_PRODUCCION',
            "Usuario consultó detalles del presupuesto #{$presupuesto->id}"
        );

        return view('presupuestos-produccion.show', compact('presupuesto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Verificar permisos
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para editar presupuestos.');
        }

        $presupuesto = PresupuestoProduccion::with(['usuarioRegistro', 'pedido'])
            ->findOrFail($id);

        // Verificar si puede ser modificado
        if (!$presupuesto->puedeSerModificado()) {
            return redirect()->route('presupuestos-produccion.show', $presupuesto->id)
                ->with('error', 'Este presupuesto no puede ser modificado debido a su estado actual.');
        }

        // Obtener tipos para sugerencias
        $tiposPrenda = PresupuestoProduccion::select('tipo_prenda')
            ->distinct()
            ->orderBy('tipo_prenda')
            ->pluck('tipo_prenda');

        $tiposTela = PresupuestoProduccion::select('tipo_tela')
            ->distinct()
            ->orderBy('tipo_tela')
            ->pluck('tipo_tela');

        return view('presupuestos-produccion.edit', compact('presupuesto', 'tiposPrenda', 'tiposTela'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Verificar permisos
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para editar presupuestos.');
        }

        $presupuesto = PresupuestoProduccion::findOrFail($id);

        // Verificar si puede ser modificado
        if (!$presupuesto->puedeSerModificado()) {
            return redirect()->route('presupuestos-produccion.show', $presupuesto->id)
                ->with('error', 'Este presupuesto no puede ser modificado debido a su estado actual.');
        }

        // Validar datos (misma validación que store)
        $request->validate([
            'tipo_prenda' => 'required|string|max:255',
            'tipo_tela' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            
            // Costos de materiales
            'costo_tela' => 'required|numeric|min:0',
            'costo_cierre' => 'required|numeric|min:0',
            'costo_boton' => 'required|numeric|min:0',
            'costo_bolsa' => 'required|numeric|min:0',
            'costo_hilo' => 'required|numeric|min:0',
            'costo_etiqueta_cinta' => 'required|numeric|min:0',
            'costo_etiqueta_carton' => 'required|numeric|min:0',
            
            // Costos de mano de obra
            'costo_tallerista' => 'required|numeric|min:0',
            'costo_planchado' => 'required|numeric|min:0',
            'costo_ayudante' => 'required|numeric|min:0',
            'costo_cortador' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $presupuesto) {
                $datosAnteriores = $presupuesto->toArray();
                
                // Actualizar datos
                $presupuesto->fill($request->all());
                
                // Recalcular totales
                $presupuesto->actualizarTotales();
                
                // Validar regla de negocio
                if ($presupuesto->costo_total <= 0) {
                    throw new \Exception('El costo total del presupuesto debe ser mayor a cero.');
                }
                
                $presupuesto->save();

                // Registrar en bitácora
                $this->bitacoraService->registrarActividad(
                    'UPDATE',
                    'PRESUPUESTOS_PRODUCCION',
                    "Se actualizó el presupuesto #{$presupuesto->id} - Nuevo total: {$presupuesto->costo_total_formateado}",
                    $datosAnteriores,
                    $presupuesto->fresh()->toArray()
                );
            });

            return redirect()->route('presupuestos-produccion.show', $presupuesto->id)
                ->with('success', "Presupuesto actualizado exitosamente. Nuevo total: {$presupuesto->costo_total_formateado}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado del presupuesto
     */
    public function cambiarEstado(Request $request, string $id)
    {
        // Verificar permisos (solo administradores pueden aprobar)
        if (!Auth::check() || Auth::user()->id_rol !== 1) {
            abort(403, 'Solo los administradores pueden cambiar el estado de presupuestos.');
        }

        $request->validate([
            'estado' => 'required|string|in:Borrador,Aprobado,Utilizado'
        ]);

        $presupuesto = PresupuestoProduccion::findOrFail($id);
        $estadoAnterior = $presupuesto->estado;

        $presupuesto->update(['estado' => $request->estado]);

        // Registrar cambio de estado
        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PRESUPUESTOS_PRODUCCION',
            "Se cambió el estado del presupuesto #{$presupuesto->id} de '{$estadoAnterior}' a '{$request->estado}'",
            ['estado' => $estadoAnterior],
            ['estado' => $request->estado]
        );

        return redirect()->route('presupuestos-produccion.show', $presupuesto->id)
            ->with('success', "Estado del presupuesto cambiado a: {$request->estado}");
    }

    /**
     * Duplicar presupuesto como base para uno nuevo
     */
    public function duplicar(string $id)
    {
        // Verificar permisos
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para duplicar presupuestos.');
        }

        $presupuestoOriginal = PresupuestoProduccion::findOrFail($id);

        // Crear nuevo presupuesto basado en el original
        $nuevoPresupuesto = $presupuestoOriginal->replicate();
        $nuevoPresupuesto->id_usuario_registro = Auth::user()->id_usuario;
        $nuevoPresupuesto->estado = 'Borrador';
        $nuevoPresupuesto->id_pedido = null; // Limpiar asociación con pedido
        $nuevoPresupuesto->tipo_prenda = $nuevoPresupuesto->tipo_prenda . ' (Copia)';
        $nuevoPresupuesto->save();

        // Registrar duplicación
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PRESUPUESTOS_PRODUCCION',
            "Se duplicó el presupuesto #{$presupuestoOriginal->id} como nuevo presupuesto #{$nuevoPresupuesto->id}",
            null,
            $nuevoPresupuesto->toArray()
        );

        return redirect()->route('presupuestos-produccion.edit', $nuevoPresupuesto->id)
            ->with('success', 'Presupuesto duplicado exitosamente. Puedes modificarlo según tus necesidades.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Verificar permisos (solo administradores)
        if (!Auth::check() || Auth::user()->id_rol !== 1) {
            abort(403, 'Solo los administradores pueden eliminar presupuestos.');
        }

        $presupuesto = PresupuestoProduccion::findOrFail($id);

        // Solo se pueden eliminar presupuestos en borrador
        if ($presupuesto->estado !== 'Borrador') {
            return redirect()->route('presupuestos-produccion.index')
                ->with('error', 'Solo se pueden eliminar presupuestos en estado Borrador.');
        }

        $datosPresupuesto = $presupuesto->toArray();
        $presupuesto->delete();

        // Registrar eliminación
        $this->bitacoraService->registrarActividad(
            'DELETE',
            'PRESUPUESTOS_PRODUCCION',
            "Se eliminó el presupuesto #{$id} - {$datosPresupuesto['tipo_prenda']}",
            $datosPresupuesto,
            null
        );

        return redirect()->route('presupuestos-produccion.index')
            ->with('success', 'Presupuesto eliminado exitosamente.');
    }
}