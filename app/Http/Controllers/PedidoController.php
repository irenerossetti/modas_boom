<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Prenda;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PedidoController extends Controller
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
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

        // Construir consulta con filtros optimizada
        $query = Pedido::with(['cliente' => function($query) {
                $query->select('id', 'nombre', 'apellido', 'ci_nit');
            }])
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
        ]);

        $pedido = Pedido::create([
            'id_cliente' => $request->id_cliente,
            'estado' => 'En proceso',
            'total' => $request->total,
        ]);

        // Registrar creación en bitácora
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PEDIDOS',
            "Se creó el pedido #{$pedido->id_pedido} para el cliente {$pedido->nombre_completo_cliente}",
            null,
            $pedido->toArray()
        );

        return redirect()->route('pedidos.index')
            ->with('success', "Pedido #{$pedido->id_pedido} creado exitosamente.");
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
        $pedido = Pedido::with(['cliente', 'prendas'])->findOrFail($id);

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

        return redirect()->route('pedidos.mis-pedidos')
            ->with('success', "¡Pedido #{$pedido->id_pedido} creado exitosamente! Stock actualizado automáticamente. Te contactaremos pronto para confirmar los detalles.");
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
        $query = Pedido::select('id_pedido', 'id_cliente', 'estado', 'total', 'created_at', 'updated_at')
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
                       ->where('activo', true)
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

        return redirect()->route('pedidos.index')
            ->with('success', $successMessage);
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
}
    // ========== CU19: REPROGRAMAR ENTREGA ==========

    /**
     * Mostrar formulario para reprogramar entrega
     */
    public function reprogramarEntrega(string $id)
    {
        $pedido = Pedido::with(['cliente', 'prendas'])->findOrFail($id);

        if (!$pedido->puedeReprogramarEntrega()) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Este pedido no puede reprogramar su entrega en el estado actual.');
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

        if (!in_array($pedido->estado, ['Asignado', 'En producción'])) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)
                ->with('error', 'Solo se pueden registrar avances en pedidos Asignados o En producción.');
        }

        $etapas = AvanceProduccion::getEtapasDisponibles();
        $avancesAnteriores = $pedido->avancesProduccion()->orderBy('created_at', 'desc')->get();

        return view('pedidos.registrar-avance', compact('pedido', 'etapas', 'avancesAnteriores'));
    }

    /**
     * Procesar registro de avance
     */
    public function procesarAvance(Request $request, string $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'etapa' => 'required|string|in:Corte,Confección,Acabado,Control de Calidad',
            'porcentaje_avance' => 'required|integer|min:0|max:100',
            'descripcion' => 'required|string|max:500',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $avance = AvanceProduccion::create([
            'id_pedido' => $pedido->id_pedido,
            'etapa' => $request->etapa,
            'porcentaje_avance' => $request->porcentaje_avance,
            'descripcion' => $request->descripcion,
            'observaciones' => $request->observaciones,
            'registrado_por' => Auth::user()->id_usuario,
        ]);

        // Cambiar estado si es necesario
        if ($pedido->estado === 'Asignado') {
            $pedido->update(['estado' => 'En producción']);
        }

        // Registrar en bitácora
        $mensaje = Auth::user()->nombre . " registró avance de producción para el pedido #{$pedido->id_pedido}";
        $mensaje .= " - Etapa: {$request->etapa} ({$request->porcentaje_avance}%)";

        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PRODUCCION',
            $mensaje,
            null,
            $avance->toArray()
        );

        return redirect()->route('pedidos.show', $pedido->id_pedido)
            ->with('success', "Avance de {$request->etapa} registrado exitosamente ({$request->porcentaje_avance}%)");
    }

    /**
     * Ver historial de avances
     */
    public function historialAvances(string $id)
    {
        $pedido = Pedido::with(['cliente', 'avancesProduccion.registradoPor'])->findOrFail($id);
        $avances = $pedido->avancesProduccion()->with('registradoPor')->orderBy('created_at', 'desc')->get();

        return view('pedidos.historial-avances', compact('pedido', 'avances'));
    }