<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Prenda;
use App\Models\ObservacionCalidad;
use App\Models\AvanceProduccion;
use App\Services\BitacoraService;
use App\Services\EmailService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PedidoController extends Controller
{
    protected $bitacoraService;
    protected $emailService;
    protected $whatsAppService;

    public function __construct(BitacoraService $bitacoraService, EmailService $emailService, WhatsAppService $whatsAppService)
    {
        $this->bitacoraService = $bitacoraService;
        $this->emailService = $emailService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Obtener productos del catálogo desde la base de datos (cacheado para mejor rendimiento)
     */
    private function getProductosCatalogo()
    {
        return Cache::remember('productos_catalogo_db', 3600, function () {
            return \App\Models\Prenda::activas()
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get()
                ->map(function ($prenda) {
                    return [
                        'id' => $prenda->id,
                        'nombre' => $prenda->nombre,
                        'precio' => $prenda->precio,
                        'categoria' => $prenda->categoria,
                        'imagen' => $prenda->imagen,
                        'descripcion' => $prenda->descripcion,
                        'colores' => $prenda->colores ?? [],
                        'tallas' => $prenda->tallas ?? [],
                        'stock' => $prenda->stock
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Validar filtros
        $request->validate([
            'estado' => 'nullable|string|in:En proceso,Asignado,En producción,Terminado,Entregado,Cancelado',
            'id_cliente' => 'nullable|integer|exists:clientes,id',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'busqueda' => 'nullable|string|max:255',
        ]);

        // Obtener filtros del request
        $filtros = $request->only(['estado', 'id_cliente', 'fecha_desde', 'fecha_hasta', 'busqueda']);
        
        // Remover filtros vacíos
        $filtros = array_filter($filtros, function($value) {
            return !is_null($value) && $value !== '';
        });

        // Sólo administradores pueden filtrar por estado (CU24)
        if (!Auth::check() || Auth::user()->id_rol !== 1) {
            unset($filtros['estado']);
        }

        // Construir consulta con filtros optimizada
        $query = Pedido::with([
                'cliente' => function($query) {
                    $query->select('id', 'nombre', 'apellido', 'ci_nit');
                },
                'pagos' => function($query) {
                    $query->select('id', 'id_pedido', 'monto', 'anulado');
                }
            ])
            ->select('id_pedido', 'id_cliente', 'estado', 'total', 'created_at', 'updated_at')
            ->byEstado($filtros['estado'] ?? null)
            ->byCliente($filtros['id_cliente'] ?? null)
            ->byFechas($filtros['fecha_desde'] ?? null, $filtros['fecha_hasta'] ?? null)
            ->buscar($filtros['busqueda'] ?? null)
            ->orderBy('id_pedido', 'desc');

        $pedidos = $query->paginate(15);

        // Obtener datos para los filtros (solo clientes que tienen pedidos) - con cache
        $clientes = Cache::remember('clientes_con_pedidos', 300, function () {
            return Cliente::select('id', 'nombre', 'apellido')
                ->whereHas('pedidos')
                ->orderBy('nombre')
                ->get();
        });
        
        $estados = Pedido::getEstadosDisponibles();

        // Registrar acceso a pedidos
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'PEDIDOS',
            'Usuario accedió a la lista de pedidos'
        );

        try {
            $this->whatsAppService->enviarConfirmacionPedido($pedido);
            if ($pedido->fecha_entrega_programada) {
                $this->whatsAppService->enviarNotificacionEntregaProgramada($pedido, null, Carbon::parse($pedido->fecha_entrega_programada));
            }
        } catch (\Exception $e) {
            \Log::error('Error enviando confirmación por WhatsApp en empleadoStore(): ' . $e->getMessage());
        }
        return view('pedidos.index', compact('pedidos', 'clientes', 'estados', 'filtros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $clientes = Cliente::select('id', 'nombre', 'apellido', 'ci_nit')
            ->orderBy('nombre')
            ->get();

        // Cliente preseleccionado si viene desde la vista de clientes
        $clienteSeleccionado = $request->get('cliente');
        
        // Producto preseleccionado si viene desde el catálogo
        $productoSeleccionado = [
            'nombre' => $request->get('producto'),
            'precio' => $request->get('total'),
            'categoria' => $request->get('categoria')
        ];

        return view('pedidos.create', compact('clientes', 'clienteSeleccionado', 'productoSeleccionado'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|integer|exists:clientes,id',
            'total' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string|max:1000',
            'fecha_entrega_programada' => 'nullable|date|after:today',
        ]);

        $pedido = Pedido::create([
            'id_cliente' => $request->id_cliente,
            'estado' => 'En proceso',
            'total' => $request->total,
            'fecha_entrega_programada' => $request->fecha_entrega_programada ?? null,
        ]);

        // Registrar creación en bitácora
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PEDIDOS',
            "Se creó el pedido #{$pedido->id_pedido} para el cliente {$pedido->nombre_completo_cliente}",
            null,
            $pedido->toArray()
        );

        // Enviar confirmación por WhatsApp
        try {
            $this->whatsAppService->enviarConfirmacionPedido($pedido);
            if ($pedido->fecha_entrega_programada) {
                $this->whatsAppService->enviarNotificacionEntregaProgramada($pedido, null, Carbon::parse($pedido->fecha_entrega_programada));
            }
        } catch (\Exception $e) {
            \Log::error('Error enviando confirmación por WhatsApp en store(): ' . $e->getMessage());
        }

        return redirect()->route('pagos.checkout', $pedido->id_pedido)
            ->with('success', "Pedido #{$pedido->id_pedido} creado exitosamente. Procede con el pago.");
    }

    /**
     * Crear pedido directamente desde el catálogo
     */
    public function createFromCatalog(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'producto_nombre' => 'required|string|max:255',
            'producto_precio' => 'required|numeric|min:0',
            'categoria' => 'nullable|string|max:100',
        ]);

        $cliente = Cliente::findOrFail($request->cliente_id);
        
        // Crear descripción del producto
        $descripcion = "Producto: {$request->producto_nombre}";
        if ($request->categoria) {
            $descripcion .= "\nCategoría: {$request->categoria}";
        }
        $descripcion .= "\nPedido realizado desde el catálogo web";

        $pedido = Pedido::create([
            'id_cliente' => $request->cliente_id,
            'estado' => 'En proceso',
            'total' => $request->producto_precio,
        ]);

        // Registrar creación en bitácora
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PEDIDOS',
            "Se creó el pedido #{$pedido->id_pedido} desde el catálogo para {$cliente->nombre} {$cliente->apellido} - Producto: {$request->producto_nombre}",
            null,
            array_merge($pedido->toArray(), [
                'producto' => $request->producto_nombre,
                'categoria' => $request->categoria,
                'origen' => 'catalogo'
            ])
        );

        try {
            $this->whatsAppService->enviarConfirmacionPedido($pedido);
        } catch (\Exception $e) {
            \Log::error('Error enviando confirmación por WhatsApp en createFromCatalog(): ' . $e->getMessage());
        }

        return redirect()->route('catalogo.pedido-confirmado', $pedido->id_pedido)
            ->with('success', "¡Pedido #{$pedido->id_pedido} creado exitosamente!");
    }

    /**
     * Mostrar confirmación de pedido desde catálogo
     */
    public function pedidoConfirmado($id)
    {
        $pedido = Pedido::with('cliente')->findOrFail($id);
        
        return view('catalogo.pedido-confirmado', compact('pedido'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Cargar devoluciones para mostrar conteos y detalles en la vista
        $pedido = Pedido::with(['cliente', 'prendas', 'devoluciones.prenda', 'devoluciones.registradoPor', 'pagos.registradoPor'])->findOrFail($id);

        // Registrar visualización
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'PEDIDOS',
            "Usuario consultó detalles del pedido #{$pedido->id_pedido}"
        );

        return view('pedidos.show', compact('pedido'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pedido = Pedido::with('cliente')->findOrFail($id);

        if (!$pedido->puedeSerEditado()) {
            return redirect()->route('pedidos.index')
                ->with('error', 'Este pedido no puede ser editado debido a su estado actual.');
        }

        $clientes = Cliente::select('id', 'nombre', 'apellido', 'ci_nit')
            ->orderBy('nombre')
            ->get();
        
        $estados = Pedido::getEstadosDisponibles();

        return view('pedidos.edit', compact('pedido', 'clientes', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pedido = Pedido::findOrFail($id);

        if (!$pedido->puedeSerEditado()) {
            return redirect()->route('pedidos.index')
                ->with('error', 'Este pedido no puede ser editado debido a su estado actual.');
        }

        $request->validate([
            'id_cliente' => 'required|integer|exists:clientes,id',
            'estado' => 'required|string|in:En proceso,Asignado,En producción,Terminado,Entregado,Cancelado',
            'total' => 'nullable|numeric|min:0',
        ]);

        $datosAnteriores = $pedido->toArray();
        $estadoAnterior = $pedido->estado;
        $nuevoEstado = $request->estado;
        
        try {
            // Usar transacción para manejar cambios de stock si es necesario
            \DB::transaction(function () use ($pedido, $request, $estadoAnterior, $nuevoEstado) {
            // Si se está cambiando a "Cancelado" y antes no estaba cancelado, restaurar stock
            if ($nuevoEstado === 'Cancelado' && $estadoAnterior !== 'Cancelado') {
                foreach ($pedido->prendas as $prenda) {
                    $cantidadUnidades = $prenda->pivot->cantidad ?? 0;
                    if ($cantidadUnidades > 0) {
                        $cantidadDocenas = $cantidadUnidades / 12; // Convertir unidades a docenas
                        $prenda->restaurarStock($cantidadDocenas);
                    }
                }
            }
            
            // Si se está cambiando de "Cancelado" a otro estado, descontar stock nuevamente
            if ($estadoAnterior === 'Cancelado' && $nuevoEstado !== 'Cancelado') {
                foreach ($pedido->prendas as $prenda) {
                    $cantidadUnidades = $prenda->pivot->cantidad ?? 0;
                    if ($cantidadUnidades > 0) {
                        $cantidadDocenas = $cantidadUnidades / 12; // Convertir unidades a docenas
                        if (!$prenda->tieneStock($cantidadDocenas)) {
                            throw new \Exception("Stock insuficiente para '{$prenda->nombre}'. Disponible: {$prenda->stock} docenas, Necesario: {$cantidadDocenas} docenas");
                        }
                        $prenda->descontarStock($cantidadDocenas);
                    }
                }
            }
            
            // Actualizar el pedido
            $pedido->update([
                'id_cliente' => $request->id_cliente,
                'estado' => $nuevoEstado,
                'total' => $request->total,
                'fecha_entrega_programada' => $request->fecha_entrega_programada ?? $pedido->fecha_entrega_programada,
            ]);
        });
        } catch (\Exception $e) {
            return redirect()->route('pedidos.index')
                ->with('error', 'Error al actualizar el pedido: ' . $e->getMessage());
        }

        // Preparar mensaje de bitácora
        $mensaje = "Se actualizó el pedido #{$pedido->id_pedido}";
        if ($estadoAnterior !== $nuevoEstado) {
            $mensaje .= " - Estado: {$estadoAnterior} → {$nuevoEstado}";
            
            if ($nuevoEstado === 'Cancelado' && $estadoAnterior !== 'Cancelado') {
                $mensaje .= " - Stock restaurado automáticamente";
            } elseif ($estadoAnterior === 'Cancelado' && $nuevoEstado !== 'Cancelado') {
                $mensaje .= " - Stock descontado nuevamente";
            }
        }

        // Registrar actualización en bitácora
        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PEDIDOS',
            $mensaje,
            $datosAnteriores,
            $pedido->fresh()->toArray()
        );

        // Notificar al cliente por WhatsApp acerca de la reprogramación
        try {
            $this->whatsAppService->enviarNotificacionEntregaProgramada($pedido, $fechaAnterior ? \Carbon\Carbon::parse($fechaAnterior) : null, \Carbon\Carbon::parse($request->nueva_fecha_entrega), $request->motivo_reprogramacion);
        } catch (\Exception $e) {
            \Log::error('Error enviando notificación de reprogramación por WhatsApp: ' . $e->getMessage());
        }

        // Enviar notificaciones por WhatsApp si hubo cambio de estado
        if ($estadoAnterior !== $nuevoEstado) {
            try {
                // Construir lista de campos cambiados exactos
                $camposCambiados = [];
                $changes = $pedido->getChanges();
                foreach ($changes as $field => $newVal) {
                    $camposCambiados[$field] = [
                        'antes' => $datosAnteriores[$field] ?? null,
                        'despues' => $newVal
                    ];
                }

                if ($nuevoEstado === 'Terminado') {
                    $this->whatsAppService->enviarNotificacionTerminado($pedido, $camposCambiados);
                } elseif ($nuevoEstado === 'Entregado') {
                    $this->whatsAppService->enviarNotificacionEntregado($pedido, $camposCambiados);
                } else {
                    $this->whatsAppService->enviarNotificacionEstado($pedido, $nuevoEstado, null, $camposCambiados);
                }

                // Notificar si se estableció o cambió fecha de entrega
                if ($request->has('fecha_entrega_programada') && $request->fecha_entrega_programada) {
                    $fechaAnterior = $datosAnteriores['fecha_entrega_programada'] ?? null;
                    $fechaNueva = $pedido->fecha_entrega_programada;
                    if (!$fechaAnterior || $fechaAnterior != $fechaNueva) {
                        $this->whatsAppService->enviarNotificacionEntregaProgramada($pedido, $fechaAnterior ? Carbon::parse($fechaAnterior) : null, Carbon::parse($fechaNueva));
                    }
                }

            } catch (\Exception $e) {
                \Log::error('Error enviando notificación por WhatsApp en update(): ' . $e->getMessage());
            }
        }

        $successMessage = "Pedido #{$pedido->id_pedido} actualizado exitosamente.";
        if ($estadoAnterior !== $nuevoEstado) {
            if ($nuevoEstado === 'Cancelado') {
                $successMessage .= " Stock restaurado automáticamente.";
            } elseif ($estadoAnterior === 'Cancelado') {
                $successMessage .= " Stock actualizado automáticamente.";
            }
        }

        return redirect()->route('pedidos.index')
            ->with('success', $successMessage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pedido = Pedido::findOrFail($id);

        if (!$pedido->puedeSerCancelado()) {
            return redirect()->route('pedidos.index')
                ->with('error', 'Este pedido no puede ser eliminado debido a su estado actual.');
        }

        $datosAnteriores = $pedido->toArray();
        
        try {
            // Usar transacción para restaurar stock y cancelar pedido
            \DB::transaction(function () use ($pedido) {
                // Restaurar stock de todas las prendas del pedido
                foreach ($pedido->prendas as $prenda) {
                    $cantidadUnidades = $prenda->pivot->cantidad ?? 0;
                    if ($cantidadUnidades > 0) {
                        $cantidadDocenas = $cantidadUnidades / 12; // Convertir unidades a docenas
                        $prenda->restaurarStock($cantidadDocenas);
                    }
                }
                
                // Cambiar estado a cancelado
                $pedido->update(['estado' => 'Cancelado']);
            });
        } catch (\Exception $e) {
            return redirect()->route('pedidos.index')
                ->with('error', 'Error al cancelar el pedido: ' . $e->getMessage());
        }

        // Registrar cancelación en bitácora
        $this->bitacoraService->registrarActividad(
            'DELETE',
            'PEDIDOS',
            "Se canceló el pedido #{$pedido->id_pedido} y se restauró el stock de " . $pedido->prendas->count() . " productos",
            $datosAnteriores,
            $pedido->fresh()->toArray()
        );

        // Notificar al cliente por WhatsApp sobre la cancelación
        try {
            $this->whatsAppService->enviarNotificacionPedidoCancelado($pedido);
        } catch (\Exception $e) {
            \Log::error('Error enviando notificación de cancelación por WhatsApp en destroy(): ' . $e->getMessage());
        }

        return redirect()->route('pedidos.index')
            ->with('success', "Pedido #{$pedido->id_pedido} cancelado exitosamente. Stock restaurado automáticamente.");
    }

    /**
     * Asignar pedido a operario
     */
    public function asignar(Request $request, string $id)
    {
        // Verificar que el usuario sea administrador
        if (!Auth::check() || !Auth::user()->rol || Auth::user()->rol->nombre !== 'Administrador') {
            abort(403, 'No tienes permisos para asignar pedidos.');
        }

        $pedido = Pedido::findOrFail($id);

        if (!$pedido->puedeSerAsignado()) {
            return redirect()->route('pedidos.index')
                ->with('error', 'Este pedido no puede ser asignado debido a su estado actual.');
        }

        $request->validate([
            'id_operario' => 'required|integer|exists:usuario,id_usuario',
        ]);

        $operario = User::findOrFail($request->id_operario);
        $datosAnteriores = $pedido->toArray();

        $pedido->update(['estado' => 'Asignado']);

        // Registrar asignación en bitácora
        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PEDIDOS',
            "Se asignó el pedido #{$pedido->id_pedido} al operario {$operario->nombre}",
            $datosAnteriores,
            array_merge($pedido->fresh()->toArray(), ['operario_asignado' => $operario->nombre])
        );

        // Enviar notificación por WhatsApp sobre la asignación
        try {
            $camposCambiados = [];
            $changes = $pedido->getChanges();
            foreach ($changes as $field => $newVal) {
                $camposCambiados[$field] = [
                    'antes' => $datosAnteriores[$field] ?? null,
                    'despues' => $newVal
                ];
            }
            $this->whatsAppService->enviarNotificacionEstado($pedido, 'Asignado', null, $camposCambiados);
        } catch (\Exception $e) {
            \Log::error('Error enviando notificación por WhatsApp en asignar(): ' . $e->getMessage());
        }

        return redirect()->route('pedidos.index')
            ->with('success', "Pedido #{$pedido->id_pedido} asignado a {$operario->nombre} exitosamente.");
    }

    /**
     * Consultar pedidos por operario
     */
    public function porOperario(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (!Auth::check() || !Auth::user()->rol || Auth::user()->rol->nombre !== 'Administrador') {
            abort(403, 'No tienes permisos para consultar pedidos por operario.');
        }

        $operarios = User::whereHas('rol', function($query) {
            $query->where('nombre', 'Empleado'); // Asumiendo que los operarios tienen rol "Empleado"
        })->select('id_usuario', 'nombre')->orderBy('nombre')->get();

        $pedidos = collect();
        $operarioSeleccionado = null;

        if ($request->has('id_operario') && $request->id_operario) {
            $operarioSeleccionado = User::find($request->id_operario);
            
            // Por ahora, mostrar pedidos asignados (en el futuro se podría agregar campo operario_id)
            $pedidos = Pedido::with('cliente')
                ->where('estado', 'Asignado')
                ->orderBy('id_pedido', 'desc')
                ->paginate(15);
        }

        return view('pedidos.por-operario', compact('operarios', 'pedidos', 'operarioSeleccionado'));
    }

    /**
     * Ver historial de pedidos de un cliente
     */
    public function clienteHistorial(string $clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        
        $pedidos = Pedido::where('id_cliente', $clienteId)
            ->orderBy('id_pedido', 'desc')
            ->paginate(15);

        // Registrar consulta de historial
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'PEDIDOS',
            "Usuario consultó historial de pedidos del cliente {$cliente->nombre} {$cliente->apellido}"
        );

        return view('pedidos.cliente-historial', compact('cliente', 'pedidos'));
    }

    /**
     * Ver historial de cambios de un pedido específico
     */
    public function historial(string $id)
    {
        $pedido = Pedido::with('cliente')->findOrFail($id);

        // Búsqueda optimizada directamente en la base de datos
        $historial = \App\Models\Bitacora::with('usuario')
            ->where('modulo', 'PEDIDOS')
            ->where(function ($query) use ($pedido) {
                $idPedido = $pedido->id_pedido;
                $query->where('descripcion', 'like', "%#{$idPedido}%")
                      ->orWhere('descripcion', 'like', "%pedido {$idPedido}%")
                      ->orWhere('descripcion', 'like', "%pedido #{$idPedido}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('pedidos.historial', compact('pedido', 'historial'));
    }

    /**
     * Mostrar interfaz de creación de pedidos para clientes
     */
    public function clienteCrear()
    {
        // Verificar que el usuario sea cliente o empleado
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [2, 3])) {
            abort(403, 'No tienes permisos para acceder a esta página.');
        }

        // Obtener productos del catálogo (optimizado con cache)
        $productos = $this->getProductosCatalogo();

        return view('pedidos.cliente-crear', compact('productos'));
    }

    /**
     * Procesar pedido creado por cliente
     */
    public function clienteStore(Request $request)
    {
        // Verificar que el usuario sea cliente o empleado
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [2, 3])) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $request->validate([
            'productos_data' => 'required|string',
            'descripcion_adicional' => 'nullable|string|max:1000',
            'direccion_entrega' => 'required|string|max:500',
            'telefono_contacto' => 'required|string|max:20',
        ]);

        // Decodificar productos seleccionados
        $productosSeleccionados = json_decode($request->productos_data, true);
        
        if (empty($productosSeleccionados)) {
            return back()->withErrors(['productos_data' => 'Debe seleccionar al menos un producto.']);
        }

        // Verificar stock disponible ANTES de crear el pedido
        $erroresStock = [];
        $prendasVerificadas = [];
        
        foreach ($productosSeleccionados as $producto) {
            // Buscar la prenda en la base de datos por ID
            $prenda = Prenda::find($producto['id']);
            
            if (!$prenda) {
                $erroresStock[] = "El producto '{$producto['nombre']}' no existe.";
                continue;
            }
            
            if (!$prenda->activo) {
                $erroresStock[] = "El producto '{$prenda->nombre}' no está disponible.";
                continue;
            }
            
            $docenas = $producto['cantidad'];
            $unidades = $docenas * 12;
            
            // El stock está en docenas, comparamos directamente con las docenas solicitadas
            if (!$prenda->tieneStock($docenas)) {
                $stockUnidades = $prenda->stock * 12;
                $erroresStock[] = "Stock insuficiente para '{$prenda->nombre}'. Disponible: {$prenda->stock} docenas ({$stockUnidades} unidades), Solicitado: {$docenas} docenas ({$unidades} unidades)";
                continue;
            }
            
            $prendasVerificadas[] = [
                'prenda' => $prenda,
                'docenas' => $docenas,
                'unidades' => $unidades,
                'precio_unitario' => $prenda->precio,
                'subtotal' => $prenda->precio * $docenas,
                'talla' => $producto['talla'] ?? null,
                'color' => $producto['color'] ?? null
            ];
        }

        // Si hay errores de stock, regresar con errores
        if (!empty($erroresStock)) {
            return back()->withErrors(['stock' => $erroresStock]);
        }

        // Buscar o crear cliente basado en el usuario autenticado
        $cliente = Cliente::where('email', Auth::user()->email)->first();
        
        if (!$cliente) {
            // Crear cliente si no existe
            $cliente = Cliente::create([
                'nombre' => Auth::user()->nombre,
                'apellido' => Auth::user()->apellido ?? '',
                'email' => Auth::user()->email,
                'telefono' => $request->telefono_contacto,
                'direccion' => $request->direccion_entrega,
                'ci_nit' => 'N/A', // Se puede actualizar después
            ]);
        }

        // Calcular totales
        $totalGeneral = 0;
        $totalUnidades = 0;
        $descripcionProductos = [];

        foreach ($prendasVerificadas as $item) {
            $totalGeneral += $item['subtotal'];
            $totalUnidades += $item['unidades'];
            
            $descripcionProductos[] = "• {$item['prenda']->nombre} ({$item['prenda']->categoria}) - {$item['docenas']} docena" . ($item['docenas'] > 1 ? 's' : '') . " ({$item['unidades']} unidades) - Bs. " . number_format($item['subtotal'], 2);
        }

        // Usar transacción para asegurar consistencia
        \DB::transaction(function () use ($prendasVerificadas, $cliente, $totalGeneral, $request, &$pedido) {
            // Crear el pedido
            $pedido = Pedido::create([
                'id_cliente' => $cliente->id,
                'estado' => 'En proceso',
                'total' => $totalGeneral,
            ]);

            // Procesar cada prenda: descontar stock y crear relación
            foreach ($prendasVerificadas as $item) {
                // Descontar stock (el stock está en docenas)
                $item['prenda']->descontarStock($item['docenas']);
                
                // Crear relación en tabla pivot
                $pedido->prendas()->attach($item['prenda']->id, [
                    'cantidad' => $item['unidades'],
                    'precio_unitario' => $item['precio_unitario'],
                    'talla' => $item['talla'],
                    'color' => $item['color'],
                    'observaciones' => "Pedido de {$item['docenas']} docena" . ($item['docenas'] > 1 ? 's' : '')
                ]);
            }
        });

        // Registrar creación en bitácora
        $tipoUsuario = Auth::user()->id_rol == 2 ? 'Empleado' : 'Cliente';
        $descripcionBitacora = Auth::user()->id_rol == 2 
            ? "Empleado {$cliente->nombre} {$cliente->apellido} creó un pedido personal múltiple #{$pedido->id_pedido} - " . count($prendasVerificadas) . " productos - Total: Bs. " . number_format($totalGeneral, 2)
            : "Cliente {$cliente->nombre} {$cliente->apellido} creó un pedido múltiple #{$pedido->id_pedido} - " . count($prendasVerificadas) . " productos - Total: Bs. " . number_format($totalGeneral, 2);
        
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PEDIDOS',
            $descripcionBitacora,
            null,
            array_merge($pedido->toArray(), [
                'productos_seleccionados' => count($prendasVerificadas),
                'total_productos' => count($prendasVerificadas),
                'total_unidades' => $totalUnidades,
                'descripcion_adicional' => $request->descripcion_adicional,
                'direccion_entrega' => $request->direccion_entrega,
                'telefono_contacto' => $request->telefono_contacto,
                'origen' => Auth::user()->id_rol == 2 ? 'empleado_personal_multiple' : 'cliente_plataforma_multiple',
                'tipo_usuario' => $tipoUsuario
            ])
        );

        // Enviar notificación por WhatsApp con confirmación completa y listado de productos
        try {
            $pedido = $pedido->fresh();
            $this->whatsAppService->enviarConfirmacionPedido($pedido);
            if ($pedido->fecha_entrega_programada) {
                $this->whatsAppService->enviarNotificacionEntregaProgramada($pedido, null, Carbon::parse($pedido->fecha_entrega_programada));
            }
        } catch (\Exception $e) {
            \Log::error('Error enviando confirmación por WhatsApp en clienteStore(): ' . $e->getMessage());
        }

        return redirect()->route('pagos.checkout', $pedido->id_pedido)
            ->with('success', "¡Pedido #{$pedido->id_pedido} creado exitosamente! Procede con el pago.");
    }

    /**
     * Mostrar historial de pedidos del cliente autenticado
     */
    public function misPedidos(Request $request)
    {
        // Verificar que el usuario sea cliente o empleado
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [2, 3])) {
            abort(403, 'No tienes permisos para acceder a esta página.');
        }

        // Buscar cliente basado en el usuario autenticado
        $cliente = Cliente::where('email', Auth::user()->email)->first();
        
        if (!$cliente) {
            // Si no existe cliente, mostrar mensaje
            return view('pedidos.mis-pedidos', [
                'pedidos' => collect(),
                'cliente' => null,
                'filtros' => []
            ]);
        }

        // Validar filtros
        $request->validate([
            'estado' => 'nullable|string|in:En proceso,Asignado,En producción,Terminado,Entregado,Cancelado',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        $filtros = $request->only(['estado', 'fecha_desde', 'fecha_hasta']);
        $filtros = array_filter($filtros, function($value) {
            return !is_null($value) && $value !== '';
        });

        // Obtener pedidos del cliente con filtros (optimizado)
        $query = Pedido::select('id_pedido', 'id_cliente', 'estado', 'total', 'created_at', 'updated_at', 'calificacion', 'comentario_calificacion', 'fecha_calificacion')
            ->where('id_cliente', $cliente->id)
            ->when($filtros['estado'] ?? null, function($q, $estado) {
                return $q->where('estado', $estado);
            })
            ->when($filtros['fecha_desde'] ?? null, function($q, $fecha) {
                return $q->whereDate('created_at', '>=', $fecha);
            })
            ->when($filtros['fecha_hasta'] ?? null, function($q, $fecha) {
                return $q->whereDate('created_at', '<=', $fecha);
            })
            ->orderBy('id_pedido', 'desc');

        $pedidos = $query->paginate(10);
        $estados = Pedido::getEstadosDisponibles();

        // Registrar acceso a mis pedidos
        $tipoUsuario = Auth::user()->id_rol == 2 ? 'Empleado' : 'Cliente';
        $descripcionAcceso = Auth::user()->id_rol == 2 
            ? "Empleado {$cliente->nombre} {$cliente->apellido} consultó su historial de pedidos personales"
            : "Cliente {$cliente->nombre} {$cliente->apellido} consultó su historial de pedidos";
            
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'PEDIDOS',
            $descripcionAcceso
        );

        return view('pedidos.mis-pedidos', compact('pedidos', 'cliente', 'estados', 'filtros'));
    }

    /**
     * Mostrar interfaz de creación de pedidos para empleados
     */
    public function empleadoCrear()
    {
        // Verificar que el usuario sea empleado o administrador
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para acceder a esta página.');
        }

        // Obtener clientes para selección (optimizado con cache)
        $clientes = Cache::remember('clientes_activos', 300, function () {
            return Cliente::select('id', 'nombre', 'apellido', 'ci_nit', 'telefono')
                ->orderBy('nombre')
                ->get();
        });

        // Obtener productos del catálogo (optimizado con cache)
        $productos = $this->getProductosCatalogo();

        // Obtener operarios para asignación (solo administradores) - optimizado con cache
        $operarios = [];
        if (Auth::user()->id_rol == 1) {
            $operarios = Cache::remember('operarios_activos', 600, function () {
                return User::whereHas('rol', function($query) {
                    $query->where('nombre', 'Empleado');
                })->select('id_usuario', 'nombre')->orderBy('nombre')->get();
            });
        }

        return view('pedidos.empleado-crear', compact('clientes', 'productos', 'operarios'));
    }

    /**
     * Procesar pedido creado por empleado
     */
    public function empleadoStore(Request $request)
    {
        // Verificar que el usuario sea empleado o administrador
        if (!Auth::check() || !in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $request->validate([
            'id_cliente' => 'required|integer|exists:clientes,id',
            'producto_nombre' => 'required|string|max:255',
            'producto_precio' => 'required|numeric|min:0',
            'categoria' => 'nullable|string|max:100',
            'cantidad_docenas' => 'required|integer|min:1',
            'descripcion_adicional' => 'nullable|string|max:1000',
            'direccion_entrega' => 'nullable|string|max:500',
            'telefono_contacto' => 'nullable|string|max:20',
            'id_operario' => 'nullable|integer|exists:usuario,id_usuario',
        ]);

        $cliente = Cliente::findOrFail($request->id_cliente);

        // Calcular cantidades
        $docenas = $request->cantidad_docenas;
        $unidades = $docenas * 12;
        $precioTotal = $request->producto_precio * $docenas;

        // Buscar la prenda en la base de datos por nombre y categoría
        $prenda = Prenda::where('nombre', $request->producto_nombre)
                       ->when($request->categoria, function($query, $categoria) {
                           return $query->where('categoria', $categoria);
                       })
                       ->whereRaw('"activo" = true')
                       ->first();

        // Verificar stock si la prenda existe en la BD
        if ($prenda) {
            // El stock está en docenas, comparamos directamente con las docenas solicitadas
            if (!$prenda->tieneStock($docenas)) {
                $stockUnidades = $prenda->stock * 12;
                return back()->withErrors([
                    'stock' => "Stock insuficiente para '{$prenda->nombre}'. Disponible: {$prenda->stock} docenas ({$stockUnidades} unidades), Solicitado: {$docenas} docenas ({$unidades} unidades)"
                ])->withInput();
            }
        }

        // Determinar estado inicial
        $estado = 'En proceso';
        if ($request->id_operario && Auth::user()->id_rol == 1) {
            $estado = 'Asignado';
        }

        // Usar transacción para asegurar consistencia
        \DB::transaction(function () use ($prenda, $unidades, $docenas, $request, $cliente, $estado, $precioTotal, &$pedido) {
            // Crear el pedido
            $pedido = Pedido::create([
                'id_cliente' => $request->id_cliente,
                'estado' => $estado,
                'total' => $precioTotal,
            ]);

            // Si la prenda existe en la BD, descontar stock y crear relación
            if ($prenda) {
                // Descontar stock (el stock está en docenas)
                $prenda->descontarStock($docenas);
                
                // Crear relación en tabla pivot
                $pedido->prendas()->attach($prenda->id, [
                    'cantidad' => $unidades,
                    'precio_unitario' => $request->producto_precio,
                    'observaciones' => "Pedido de {$docenas} docena" . ($docenas > 1 ? 's' : '') . " - Creado por empleado: " . Auth::user()->nombre
                ]);
            }
        });

        // Preparar datos para bitácora
        $datosAdicionales = [
            'producto' => $request->producto_nombre,
            'categoria' => $request->categoria,
            'cantidad_docenas' => $docenas,
            'cantidad_unidades' => $unidades,
            'precio_por_docena' => $request->producto_precio,
            'descripcion_adicional' => $request->descripcion_adicional,
            'direccion_entrega' => $request->direccion_entrega,
            'telefono_contacto' => $request->telefono_contacto,
            'creado_por_empleado' => Auth::user()->nombre,
            'origen' => 'empleado_plataforma',
            'stock_descontado' => $prenda ? true : false,
            'prenda_id' => $prenda ? $prenda->id : null
        ];

        $mensaje = "Empleado " . Auth::user()->nombre . " creó el pedido #{$pedido->id_pedido} para {$cliente->nombre} {$cliente->apellido} - Producto: {$request->producto_nombre} - Cantidad: {$docenas} docena" . ($docenas > 1 ? 's' : '') . " ({$unidades} unidades)";
        
        if ($prenda) {
            $mensaje .= " - Stock actualizado automáticamente";
        }

        // Si se asignó operario
        if ($request->id_operario && Auth::user()->id_rol == 1) {
            $operario = User::findOrFail($request->id_operario);
            $datosAdicionales['operario_asignado'] = $operario->nombre;
            $mensaje .= " - Asignado a: {$operario->nombre}";
        }

        // Registrar creación en bitácora
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PEDIDOS',
            $mensaje,
            null,
            array_merge($pedido->toArray(), $datosAdicionales)
        );

        $successMessage = "¡Pedido #{$pedido->id_pedido} creado exitosamente para {$cliente->nombre} {$cliente->apellido}!";
        if ($prenda) {
            $successMessage .= " Stock actualizado automáticamente.";
        }

        return redirect()->route('pagos.pasarela')
            ->with('success', $successMessage . " Procede con el pago.")
            ->with('pedido_creado', $pedido->id_pedido);
    }

    /**
     * Verificar stock de productos antes de crear pedido (AJAX)
     */
    public function verificarStock(Request $request)
    {
        $request->validate([
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.cantidad' => 'required|integer|min:1'
        ]);

        $resultados = [];
        $errores = [];

        foreach ($request->productos as $producto) {
            $prenda = Prenda::find($producto['id']);
            
            if (!$prenda) {
                $errores[] = "Producto con ID {$producto['id']} no encontrado";
                continue;
            }

            $unidades = $producto['cantidad'] * 12; // Convertir docenas a unidades
            $tieneStock = $prenda->tieneStock($unidades);

            $resultados[] = [
                'id' => $prenda->id,
                'nombre' => $prenda->nombre,
                'stock_disponible' => $prenda->stock,
                'cantidad_solicitada' => $unidades,
                'tiene_stock' => $tieneStock,
                'mensaje' => $tieneStock 
                    ? "Stock suficiente" 
                    : "Stock insuficiente. Disponible: {$prenda->stock}, Solicitado: {$unidades}"
            ];
        }

        return response()->json([
            'success' => empty($errores),
            'errores' => $errores,
            'productos' => $resultados
        ]);
    }

    /**
     * Obtener stock actual de un producto específico (AJAX)
     */
    public function obtenerStock($id)
    {
        $prenda = Prenda::find($id);
        
        if (!$prenda) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'producto' => [
                'id' => $prenda->id,
                'nombre' => $prenda->nombre,
                'stock' => $prenda->stock,
                'activo' => $prenda->activo
            ]
        ]);
    }

    // ========== CU19: REPROGRAMAR ENTREGA ==========

    /**
     * Mostrar formulario para reprogramar entrega
     */
    public function reprogramarEntrega(string $id)
    {
        $pedido = Pedido::with(['cliente', 'prendas'])->findOrFail($id);

        // Solo permitir si el pedido está en un estado reprogramable
        if (!$pedido->puedeReprogramarEntrega()) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Este pedido no puede reprogramar su entrega en el estado actual.');
        }
        // Verificar rol: administradores pueden reprogramar cualquier pedido.
        // Clientes sólo pueden reprogramar sus propios pedidos.
        $user = Auth::user();
        if ($user->id_rol === 3) { // cliente
            if ($pedido->id_cliente != $user->id_usuario) {
                return redirect()->route('pedidos.show', $pedido->id_pedido)
                    ->with('error', 'No tienes permiso para reprogramar este pedido.');
            }
        }
        $historialReprogramaciones = $pedido->historialReprogramaciones();

        return view('pedidos.reprogramar-entrega', compact('pedido', 'historialReprogramaciones'));
    }

    /**
     * Procesar reprogramación de entrega
     */
    public function procesarReprogramacion(Request $request, string $id)
    {
        $pedido = Pedido::findOrFail($id);

        if (!$pedido->puedeReprogramarEntrega()) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Este pedido no puede reprogramar su entrega.');
        }

        // Sólo administradores o clientes propietarios
        $user = Auth::user();
        if ($user->id_rol === 3 && $pedido->id_cliente != $user->id_usuario) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'No tienes permiso para reprogramar este pedido.');
        }

        $request->validate([
            'nueva_fecha_entrega' => 'required|date|after:today',
            'motivo_reprogramacion' => 'required|string|max:500',
        ]);

        $fechaAnterior = $pedido->fecha_entrega_programada;
        
        $pedido->update([
            'fecha_entrega_programada' => $request->nueva_fecha_entrega,
            'observaciones_entrega' => $request->motivo_reprogramacion,
            'reprogramado_por' => Auth::user()->id_usuario,
            'fecha_reprogramacion' => now(),
        ]);

        // Registrar en bitácora
        $mensaje = Auth::user()->nombre . " reprogramó la entrega del pedido #{$pedido->id_pedido}";
        $mensaje .= " de " . ($fechaAnterior ? $fechaAnterior->format('d/m/Y') : 'sin fecha');
        $mensaje .= " a " . \Carbon\Carbon::parse($request->nueva_fecha_entrega)->format('d/m/Y');
        $mensaje .= " - Motivo: " . $request->motivo_reprogramacion;

        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PEDIDOS',
            $mensaje,
            $pedido->getOriginal(),
            $pedido->fresh()->toArray()
        );

        return redirect()->route('pedidos.show', $pedido->id_pedido)
            ->with('success', 'Entrega reprogramada exitosamente para el ' . 
                   \Carbon\Carbon::parse($request->nueva_fecha_entrega)->format('d/m/Y'));
    }

    /**
     * Ver historial de reprogramaciones
     */
    public function historialReprogramaciones(string $id)
    {
        $pedido = Pedido::with('cliente')->findOrFail($id);
        $reprogramaciones = $pedido->historialReprogramaciones();

        return view('pedidos.historial-reprogramaciones', compact('pedido', 'reprogramaciones'));
    }

    // ========== CU20: REGISTRAR AVANCE DE PRODUCCIÓN ==========

    /**
     * Mostrar formulario para registrar avance
     */
    public function registrarAvance(string $id)
    {
        $pedido = Pedido::with(['cliente', 'avancesProduccion.registradoPor'])->findOrFail($id);

        // Acceso exclusivo para administradores (CU20)
        if (Auth::user()->id_rol !== 1) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Solo administradores pueden registrar avances de producción.');
        }

        if (!in_array($pedido->estado, ['Asignado', 'En producción'])) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Solo se pueden registrar avances en pedidos Asignados o En producción.');
        }

        $etapas = AvanceProduccion::getEtapasDisponibles();
        $avancesAnteriores = $pedido->avancesProduccion()->orderBy('created_at', 'desc')->get();
        
        // Obtener empleados (operarios) para asignar - NUEVO
        $operarios = User::where('id_rol', 2)->where('habilitado', true)->orderBy('nombre')->get();

        return view('pedidos.registrar-avance', compact('pedido', 'etapas', 'avancesAnteriores', 'operarios'));
    }

    /**
     * Procesar registro de avance
     */
    public function procesarAvance(Request $request, string $id)
    {
        $pedido = Pedido::findOrFail($id);

        // Acceso exclusivo para administradores
        if (Auth::user()->id_rol !== 1) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'No tienes permiso para registrar avances.');
        }

        $request->validate([
            'etapa' => 'required|string|in:Corte,Confección,Acabado,Control de Calidad',
            'porcentaje_avance' => 'required|integer|min:0|max:100',
            'descripcion' => 'required|string|max:500',
            'observaciones' => 'nullable|string|max:1000',
            'operario_id' => 'nullable|exists:usuario,id_usuario',  // NUEVO
            'costo_mano_obra' => 'nullable|numeric|min:0',          // NUEVO
        ]);

        $avance = AvanceProduccion::create([
            'id_pedido' => $pedido->id_pedido,
            'etapa' => $request->etapa,
            'porcentaje_avance' => $request->porcentaje_avance,
            'descripcion' => $request->descripcion,
            'observaciones' => $request->observaciones,
            'registrado_por' => Auth::user()->id_usuario,
            'user_id_operario' => $request->operario_id,    // NUEVO
            'costo_mano_obra' => $request->costo_mano_obra, // NUEVO
        ]);

        // Cambiar estado si es necesario
        if ($pedido->estado === 'Asignado') {
            $pedido->update(['estado' => 'En producción']);
        }

        // Registrar en bitácora con información del operario y costo
        $operarioNombre = $request->operario_id ? User::find($request->operario_id)->nombre : 'Sin asignar';
        $costoTexto = $request->costo_mano_obra ? " - Costo: Bs. " . number_format($request->costo_mano_obra, 2) : '';
        
        $mensaje = Auth::user()->nombre . " registró avance de producción para el pedido #{$pedido->id_pedido}";
        $mensaje .= " - Etapa: {$request->etapa} ({$request->porcentaje_avance}%)";
        $mensaje .= " - Operario: {$operarioNombre}{$costoTexto}";

        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PRODUCCION',
            $mensaje,
            null,
            $avance->toArray()
        );

        // Enviar notificación por WhatsApp con el avance (porcentaje)
        try {
            $camposCambiados = [];
            $changes = $pedido->getChanges();
            foreach ($changes as $field => $newVal) {
                $camposCambiados[$field] = [
                    'antes' => $pedido->getOriginal($field) ?? null,
                    'despues' => $newVal
                ];
            }
            $this->whatsAppService->enviarNotificacionEstado($pedido->fresh(), 'En producción', $request->porcentaje_avance, $camposCambiados);
        } catch (\Exception $e) {
            \Log::error('Error enviando notificación de avance por WhatsApp: ' . $e->getMessage());
        }

        return redirect()->route('pedidos.show', $pedido->id_pedido)
            ->with('success', "Avance de {$request->etapa} registrado exitosamente ({$request->porcentaje_avance}%)");
    }

    /**
     * Ver historial de avances
     */
    public function historialAvances(string $id)
    {
        $pedido = Pedido::with(['cliente', 'avancesProduccion.registradoPor', 'avancesProduccion.operario'])->findOrFail($id);
        // Acceso exclusivo para administradores
        if (Auth::user()->id_rol !== 1) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'No tienes permisos para ver el historial de avances.');
        }

        $avances = $pedido->avancesProduccion()->with(['registradoPor', 'operario'])->orderBy('created_at', 'desc')->get();

        return view('pedidos.historial-avances', compact('pedido', 'avances'));
    }
    // ========== CU21: REGISTRAR OBSERVACIÓN DE CALIDAD ==========

    /**
     * Mostrar formulario para registrar observación de calidad
     */
    public function registrarObservacionCalidad(string $id)
    {
        $pedido = Pedido::with(['cliente', 'observacionesCalidad.registradoPor'])->findOrFail($id);

        if (!in_array($pedido->estado, ['En producción', 'Terminado'])) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Solo se pueden registrar observaciones de calidad en pedidos En producción o Terminados.');
        }

        $tiposObservacion = ObservacionCalidad::getTiposObservacion();
        $prioridades = ObservacionCalidad::getPrioridades();
        $observacionesAnteriores = $pedido->observacionesCalidad()->orderBy('created_at', 'desc')->get();

        return view('pedidos.registrar-observacion-calidad', compact('pedido', 'tiposObservacion', 'prioridades', 'observacionesAnteriores'));
    }

    /**
     * Procesar registro de observación de calidad
     */
    public function procesarObservacionCalidad(Request $request, string $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'tipo_observacion' => 'required|string|in:Defecto,Mejora,Aprobado,Rechazado',
            'area_afectada' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'prioridad' => 'required|string|in:Baja,Media,Alta,Crítica',
            'accion_correctiva' => 'nullable|string|max:1000',
        ]);

        $observacion = ObservacionCalidad::create([
            'id_pedido' => $pedido->id_pedido,
            'tipo_observacion' => $request->tipo_observacion,
            'area_afectada' => $request->area_afectada,
            'descripcion' => $request->descripcion,
            'prioridad' => $request->prioridad,
            'accion_correctiva' => $request->accion_correctiva,
            'registrado_por' => Auth::user()->id_usuario,
        ]);

        // Registrar en bitácora
        $mensaje = Auth::user()->nombre . " registró observación de calidad para el pedido #{$pedido->id_pedido}";
        $mensaje .= " - Tipo: {$request->tipo_observacion} - Prioridad: {$request->prioridad}";

        $this->bitacoraService->registrarActividad(
            'CREATE',
            'CALIDAD',
            $mensaje,
            null,
            $observacion->toArray()
        );

        return redirect()->route('pedidos.show', $pedido->id_pedido)
            ->with('success', "Observación de calidad registrada exitosamente (Tipo: {$request->tipo_observacion})");
    }

    /**
     * Ver historial de observaciones de calidad
     */
    public function historialObservacionesCalidad(string $id)
    {
        $pedido = Pedido::with(['cliente', 'observacionesCalidad.registradoPor', 'observacionesCalidad.corregidoPor'])->findOrFail($id);
        $observaciones = $pedido->observacionesCalidad()->with(['registradoPor', 'corregidoPor'])->orderBy('created_at', 'desc')->get();

        return view('pedidos.historial-observaciones-calidad', compact('pedido', 'observaciones'));
    }

    /**
     * Actualizar estado de observación de calidad
     */
    public function actualizarObservacionCalidad(Request $request, string $id, string $observacionId)
    {
        $pedido = Pedido::findOrFail($id);
        $observacion = ObservacionCalidad::where('id_pedido', $pedido->id_pedido)->findOrFail($observacionId);

        $request->validate([
            'estado' => 'required|string|in:Pendiente,En corrección,Corregido,Cerrado',
            'accion_correctiva' => 'nullable|string|max:1000',
        ]);

        $estadoAnterior = $observacion->estado;
        
        $observacion->update([
            'estado' => $request->estado,
            'accion_correctiva' => $request->accion_correctiva,
            'corregido_por' => Auth::user()->id_usuario,
            'fecha_correccion' => $request->estado === 'Corregido' ? now() : null,
        ]);

        // Registrar en bitácora
        $mensaje = Auth::user()->nombre . " actualizó observación de calidad #{$observacion->id}";
        $mensaje .= " del pedido #{$pedido->id_pedido} de '{$estadoAnterior}' a '{$request->estado}'";

        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'CALIDAD',
            $mensaje,
            ['estado' => $estadoAnterior],
            $observacion->fresh()->toArray()
        );

        return redirect()->route('pedidos.historial-observaciones-calidad', $pedido->id_pedido)
            ->with('success', "Estado de observación actualizado a: {$request->estado}");
    }

    // ========== CU23: NOTIFICACIONES POR EMAIL ==========

    /**
     * Cambiar estado del pedido con notificación automática
     */
    public function cambiarEstadoConNotificacion(Request $request, string $id)
    {
        $pedido = Pedido::findOrFail($id);
        
        $request->validate([
            'nuevo_estado' => 'required|string|in:Pendiente,En proceso,Asignado,En producción,Terminado,Entregado,Cancelado',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $estadoAnterior = $pedido->estado;
        $estadoNuevo = $request->nuevo_estado;

        // Actualizar el pedido
        $pedido->update([
            'estado' => $estadoNuevo,
            'observaciones' => $request->observaciones
        ]);

        // Registrar en bitácora
        $mensaje = Auth::user()->nombre . " cambió el estado del pedido #{$pedido->id_pedido}";
        $mensaje .= " de '{$estadoAnterior}' a '{$estadoNuevo}'";
        if ($request->observaciones) {
            $mensaje .= " - Observaciones: " . $request->observaciones;
        }

        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PEDIDOS',
            $mensaje,
            ['estado' => $estadoAnterior],
            $pedido->fresh()->toArray()
        );

        // Enviar notificación por email
        $resultadoEmail = $this->emailService->enviarNotificacionCambioEstado($pedido, $estadoAnterior, $estadoNuevo);
        
        $mensaje = "Estado del pedido actualizado exitosamente de '{$estadoAnterior}' a '{$estadoNuevo}'.";
        
        if ($resultadoEmail['success']) {
            $mensaje .= " Notificación enviada por email a " . $resultadoEmail['email'];
        } else {
            $mensaje .= " Advertencia: No se pudo enviar la notificación por email - " . $resultadoEmail['message'];
        }

        // Determinar campos que cambiaron para enviar sólo lo necesario por WhatsApp
        $camposCambiados = [];
        $changes = $pedido->getChanges();
        foreach ($changes as $field => $newVal) {
            $camposCambiados[$field] = [
                'antes' => $pedido->getOriginal($field) ?? null,
                'despues' => $newVal
            ];
        }

        // Enviar notificación por WhatsApp (intentar siempre)
        try {
            if ($estadoNuevo === 'Terminado') {
                $this->whatsAppService->enviarNotificacionTerminado($pedido, $camposCambiados);
            } elseif ($estadoNuevo === 'Entregado') {
                $this->whatsAppService->enviarNotificacionEntregado($pedido, $camposCambiados);
            } else {
                $this->whatsAppService->enviarNotificacionEstado($pedido, $estadoNuevo, null, $camposCambiados);
            }
        } catch (\Exception $e) {
            \Log::error('Error enviando notificación por WhatsApp en cambiarEstadoConNotificacion(): ' . $e->getMessage());
        }

        return redirect()->route('pedidos.show', $pedido->id_pedido)
            ->with('success', $mensaje);
    }

    /**
     * Mostrar formulario para cambiar estado
     */
    public function mostrarCambiarEstado(string $id)
    {
        $pedido = Pedido::with('cliente')->findOrFail($id);
        
        $estadosDisponibles = [
            'Pendiente' => 'Pendiente',
            'En proceso' => 'En proceso', 
            'Asignado' => 'Asignado',
            'En producción' => 'En producción',
            'Terminado' => 'Terminado',
            'Entregado' => 'Entregado',
            'Cancelado' => 'Cancelado'
        ];

        return view('pedidos.cambiar-estado', compact('pedido', 'estadosDisponibles'));
    }

    /**
     * Probar configuración de email
     */
    public function probarEmail(string $id)
    {
        $pedido = Pedido::with('cliente')->findOrFail($id);
        
        if (!$pedido->cliente || !$pedido->cliente->email) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'El cliente no tiene email registrado.');
        }

        // Probar configuración
        $configuracion = $this->emailService->probarConfiguracion();
        
        if (!$configuracion['success']) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Error en configuración de email: ' . $configuracion['message']);
        }

        // Enviar email de prueba
        $resultado = $this->emailService->enviarConfirmacionPedido($pedido);
        
        if ($resultado['success']) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('success', 'Email de prueba enviado exitosamente a ' . $resultado['email']);
        } else {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Error enviando email de prueba: ' . $resultado['message']);
        }
    }

    // ========== CU22: CONFIRMAR RECEPCIÓN ==========

    /**
     * Confirmar recepción del pedido
     */
    public function confirmarRecepcion(Request $request, string $id)
    {
        $pedido = Pedido::findOrFail($id);
        // Verificar estado
        if ($pedido->estado !== 'Terminado') {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Solo se puede confirmar la recepción de pedidos terminados.');
        }
        // Verificar permisos: solo administradores o cliente propietario
        $user = Auth::user();
        if (!($user && ($user->id_rol == 1 || ($user->id_rol == 3 && $pedido->id_cliente == $user->id_usuario)))) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'No tienes permiso para confirmar la recepción de este pedido.');
        }
        $estadoAnterior = $pedido->estado;
        
        // Validar observaciones
        $request->validate([
            'observaciones_recepcion' => 'nullable|string|max:1000',
            'enviar_whatsapp' => 'nullable|boolean',
            'enviar_email' => 'nullable|boolean',
        ]);

        // Actualizar estado a Entregado y marcar confirmación
        $pedido->update([
            'estado' => 'Entregado',
            'recepcion_confirmada' => true,
            'fecha_confirmacion_recepcion' => now(),
            'confirmado_por' => Auth::user()->id_usuario,
            'observaciones_recepcion' => $request->observaciones_recepcion ?? null,
        ]);

        // Registrar en bitácora
        $mensaje = Auth::user()->nombre . " confirmó la recepción del pedido #{$pedido->id_pedido}";
        $mensaje .= " - Estado cambiado de '{$estadoAnterior}' a 'Entregado'";

        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PEDIDOS',
            $mensaje,
            ['estado' => $estadoAnterior],
            $pedido->fresh()->toArray()
        );

        // Enviar notificaciones según selecciones
        $mensaje = "Recepción del pedido confirmada exitosamente. Estado cambiado a 'Entregado'.";
        if ($request->boolean('enviar_whatsapp')) {
            $resultadoWhatsapp = $this->whatsAppService->enviarConfirmacionRecepcion($pedido);
            if ($resultadoWhatsapp['success']) {
                $mensaje .= " Notificación enviada por WhatsApp a " . ($pedido->cliente->telefono ?? 'N/A') . ".";
            } else {
                $mensaje .= " Advertencia WhatsApp: " . $resultadoWhatsapp['message'];
            }
        }

        if ($request->boolean('enviar_email')) {
            $resultadoEmail = $this->emailService->enviarNotificacionEntregado($pedido);
            if ($resultadoEmail['success']) {
                $mensaje .= " Notificación enviada por email a " . ($resultadoEmail['email'] ?? 'N/A') . ".";
            } else {
                $mensaje .= " Advertencia Email: " . $resultadoEmail['message'];
            }
        }

        return redirect()->route('pedidos.show', $pedido->id_pedido)
            ->with('success', $mensaje);
    }

    /**
     * Obtener datos de pedidos en formato JSON para FullCalendar
     */
    public function calendarJson()
    {
        // Obtener pedidos con fecha de entrega programada y que no estén cancelados
        $pedidos = Pedido::with('cliente')
            ->whereNotNull('fecha_entrega_programada')
            ->whereNotIn('estado', ['Cancelado'])
            ->get();

        // Formatear para FullCalendar
        $events = $pedidos->map(function ($pedido) {
            return [
                'id' => $pedido->id_pedido,
                'title' => 'Pedido #' . $pedido->id_pedido . ' - ' . $pedido->cliente->nombre,
                'start' => $pedido->fecha_entrega_programada->format('Y-m-d'),
                'color' => $pedido->calendar_color,
                'url' => route('pedidos.show', $pedido->id_pedido),
                'extendedProps' => [
                    'estado' => $pedido->estado,
                    'total' => 'Bs. ' . number_format($pedido->total, 2),
                    'cliente' => $pedido->cliente->nombre . ' ' . $pedido->cliente->apellido,
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Mostrar vista de calendario de pedidos
     */
       /**
     * Mostrar vista de calendario de pedidos
     */
    public function calendar()
    {
        return view('pedidos.calendar');
    }

    /**
     * Calificar un pedido (solo clientes)
     */
    public function calificar(Request $request, $id)
    {
        $user = Auth::user();
        
        // Verificar que sea un cliente
        if ($user->id_rol !== 3) {
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $cliente = Cliente::where('id_usuario', $user->id_usuario)->first();
        if (!$cliente) {
            return redirect()->back()->with('error', 'No se encontró información del cliente.');
        }

        $pedido = Pedido::where('id_pedido', $id)
            ->where('id_cliente', $cliente->id)
            ->first();

        if (!$pedido) {
            return redirect()->back()->with('error', 'Pedido no encontrado.');
        }

        // Verificar que el pedido esté entregado y no haya sido calificado
        if (!$pedido->puedeSerCalificado()) {
            return redirect()->back()->with('error', 'Este pedido no puede ser calificado en este momento.');
        }

        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario_calificacion' => 'nullable|string|max:500'
        ]);

        $pedido->update([
            'calificacion' => $request->calificacion,
            'comentario_calificacion' => $request->comentario_calificacion,
            'fecha_calificacion' => now()
        ]);

        // Log para debug
        \Log::info('Calificación guardada', [
            'pedido_id' => $pedido->id_pedido,
            'calificacion' => $request->calificacion,
            'comentario' => $request->comentario_calificacion,
            'pedido_calificacion' => $pedido->calificacion,
            'pedido_comentario' => $pedido->comentario_calificacion
        ]);

        // Registrar en bitácora
        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PEDIDOS',
            "Cliente calificó el pedido #{$pedido->id_pedido} con {$request->calificacion} estrellas: {$pedido->calificacion_texto}",
            null,
            $pedido->toArray()
        );

        $mensaje = $pedido->yaFueCalificado() ? 
            '¡Calificación actualizada exitosamente! Gracias por tu feedback.' : 
            '¡Gracias por tu calificación! Tu opinión nos ayuda a mejorar.';
            
        return redirect()->back()->with('success', $mensaje);
    }
}
