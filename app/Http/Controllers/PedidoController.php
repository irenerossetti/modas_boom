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
            ->orderBy('created_at', 'desc');

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
        
        $pedido->update([
            'id_cliente' => $request->id_cliente,
            'estado' => $request->estado,
            'total' => $request->total,
        ]);

        // Registrar actualización en bitácora
        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PEDIDOS',
            "Se actualizó el pedido #{$pedido->id_pedido}",
            $datosAnteriores,
            $pedido->fresh()->toArray()
        );

        return redirect()->route('pedidos.index')
            ->with('success', "Pedido #{$pedido->id_pedido} actualizado exitosamente.");
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
        
        // Usar transacción para restaurar stock y cancelar pedido
        \DB::transaction(function () use ($pedido) {
            // Restaurar stock de todas las prendas del pedido
            foreach ($pedido->prendas as $prenda) {
                $cantidad = $prenda->pivot->cantidad;
                $prenda->restaurarStock($cantidad);
            }
            
            // Cambiar estado a cancelado
            $pedido->update(['estado' => 'Cancelado']);
        });

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
                ->orderBy('created_at', 'desc')
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
            ->orderBy('created_at', 'desc')
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
            
            if (!$prenda->tieneStock($unidades)) {
                $erroresStock[] = "Stock insuficiente para '{$prenda->nombre}'. Disponible: {$prenda->stock}, Solicitado: {$unidades}";
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
                // Descontar stock
                $item['prenda']->descontarStock($item['unidades']);
                
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
            ->orderBy('created_at', 'desc');

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

        // Calcular cantidades y precio total
        $docenas = $request->cantidad_docenas;
        $unidades = $docenas * 12;
        $precioTotal = $request->producto_precio * $docenas;

        // Crear descripción del pedido
        $descripcion = "Producto: {$request->producto_nombre}";
        if ($request->categoria) {
            $descripcion .= "\nCategoría: {$request->categoria}";
        }
        $descripcion .= "\nCantidad: {$docenas} docena" . ($docenas > 1 ? 's' : '') . " ({$unidades} unidades)";
        $descripcion .= "\nPrecio por docena: Bs. " . number_format($request->producto_precio, 2);
        if ($request->descripcion_adicional) {
            $descripcion .= "\nDescripción adicional: {$request->descripcion_adicional}";
        }
        if ($request->direccion_entrega) {
            $descripcion .= "\nDirección de entrega: {$request->direccion_entrega}";
        }
        if ($request->telefono_contacto) {
            $descripcion .= "\nTeléfono de contacto: {$request->telefono_contacto}";
        }
        $descripcion .= "\nPedido creado por empleado: " . Auth::user()->nombre;

        // Determinar estado inicial
        $estado = 'En proceso';
        if ($request->id_operario && Auth::user()->id_rol == 1) {
            $estado = 'Asignado';
        }

        $pedido = Pedido::create([
            'id_cliente' => $request->id_cliente,
            'estado' => $estado,
            'total' => $precioTotal,
        ]);

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
            'origen' => 'empleado_plataforma'
        ];

        $mensaje = "Empleado " . Auth::user()->nombre . " creó el pedido #{$pedido->id_pedido} para {$cliente->nombre} {$cliente->apellido} - Producto: {$request->producto_nombre} - Cantidad: {$docenas} docena" . ($docenas > 1 ? 's' : '') . " ({$unidades} unidades)";

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

        return redirect()->route('pedidos.index')
            ->with('success', "¡Pedido #{$pedido->id_pedido} creado exitosamente para {$cliente->nombre} {$cliente->apellido}!");
    }
}
