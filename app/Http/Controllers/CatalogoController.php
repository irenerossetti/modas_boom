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
    public function index()
    {
        // Registrar acceso al catálogo
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'CATALOGO',
            'Usuario accedió al catálogo de productos'
        );

        return view('catalogo.index');
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
