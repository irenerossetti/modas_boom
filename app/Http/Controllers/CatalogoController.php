<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\BitacoraService;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    /**
     * Mostrar el catálogo de productos
     */
    public function index(Request $request)
    {
        // Validar filtros
        $request->validate([
            'busqueda' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'precio_min' => 'nullable|numeric|min:0',
            'precio_max' => 'nullable|numeric|min:0',
            'ordenar' => 'nullable|string|in:nombre_asc,nombre_desc,precio_asc,precio_desc,categoria'
        ]);

        // Obtener filtros
        $filtros = [
            'busqueda' => $request->get('busqueda'),
            'categoria' => $request->get('categoria'),
            'precio_min' => $request->get('precio_min'),
            'precio_max' => $request->get('precio_max'),
            'ordenar' => $request->get('ordenar', 'categoria')
        ];

        // Construir consulta con filtros
        $query = \App\Models\Prenda::activas();

        // Filtro de búsqueda por nombre, descripción o tipo de producto
        if ($filtros['busqueda']) {
            $query->where(function($q) use ($filtros) {
                $q->where('nombre', 'ILIKE', '%' . $filtros['busqueda'] . '%')
                  ->orWhere('descripcion', 'ILIKE', '%' . $filtros['busqueda'] . '%');
                  
                // Solo buscar en tipo_producto_detalle si existe la columna
                if (\Schema::hasColumn('prendas', 'tipo_producto_detalle')) {
                    $q->orWhere('tipo_producto_detalle', 'ILIKE', '%' . $filtros['busqueda'] . '%');
                }
            });
        }

        // Filtro por categoría
        if ($filtros['categoria']) {
            $query->where('categoria', $filtros['categoria']);
        }

        // Filtro por rango de precios
        if ($filtros['precio_min']) {
            $query->where('precio', '>=', $filtros['precio_min']);
        }
        if ($filtros['precio_max']) {
            $query->where('precio', '<=', $filtros['precio_max']);
        }

        // Ordenamiento
        switch ($filtros['ordenar']) {
            case 'nombre_asc':
                $query->orderBy('nombre', 'asc');
                break;
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            case 'precio_asc':
                $query->orderBy('precio', 'asc');
                break;
            case 'precio_desc':
                $query->orderBy('precio', 'desc');
                break;
            case 'categoria':
            default:
                $query->orderBy('categoria')->orderBy('nombre');
                break;
        }

        // Obtener productos paginados
        $productos = $query->paginate(12)->appends($request->query());

        // Obtener todas las categorías para el filtro
        $categorias = \App\Models\Prenda::activas()
            ->select('categoria')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        // Estadísticas de búsqueda
        $estadisticas = [
            'total_productos' => \App\Models\Prenda::activas()->count(),
            'productos_filtrados' => $productos->total(),
            'categorias_disponibles' => $categorias->count()
        ];

        // Registrar acceso al catálogo
        $descripcion = 'Usuario accedió al catálogo de productos';
        if ($filtros['busqueda']) {
            $descripcion .= " - Búsqueda: '{$filtros['busqueda']}'";
        }
        if ($filtros['categoria']) {
            $descripcion .= " - Categoría: '{$filtros['categoria']}'";
        }

        $this->bitacoraService->registrarActividad(
            'VIEW',
            'CATALOGO',
            $descripcion
        );

        return view('catalogo.index', compact('productos', 'categorias', 'filtros', 'estadisticas'));
    }

    /**
     * Crear pedido desde el catálogo
     */
    public function crearPedido(Request $request)
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

        $pedido = \App\Models\Pedido::create([
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
     * Mostrar confirmación de pedido
     */
    public function pedidoConfirmado($id)
    {
        $pedido = \App\Models\Pedido::with('cliente')->findOrFail($id);
        
        // Registrar visualización de confirmación
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'PEDIDOS',
            "Cliente visualizó confirmación del pedido #{$pedido->id_pedido}"
        );
        
        return view('catalogo.pedido-confirmado', compact('pedido'));
    }
}
