<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoRequest;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('cliente')->latest('id_pedido')->paginate(10);
        return view('pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        $clientes  = DB::table('clientes')->select('id','nombre','apellido')->orderBy('nombre')->get();
        $productos = DB::table('producto')->select('id_producto','nombre','precio_unitario')->where('habilitado',true)->orderBy('nombre')->get();
        return view('pedidos.create', compact('clientes','productos'));
    }

    public function store(StorePedidoRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, &$pedido) {
            $pedido = Pedido::create([
                'id_cliente'    => $data['id_cliente'],
                'fecha_pedido'  => $data['fecha_pedido'],
                'fecha_entrega' => $data['fecha_entrega'] ?? null,
                'metodo_pago'   => $data['metodo_pago'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'estado'        => 'registrado',
                'total_pedido'  => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = (float)$item['cantidad'] * (float)$item['precio_unitario'];
                $total += $subtotal;

                DetallePedido::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'id_producto'     => $item['id_producto'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal'        => $subtotal,
                ]);
            }

            $pedido->update(['total_pedido' => $total]);
        });

        return redirect()->route('pedidos.edit', $pedido->id_pedido)
            ->with('success', 'Pedido registrado correctamente.');
    }

    public function edit(Pedido $pedido)
    {
        $pedido->load('detalles','cliente');
        $clientes  = DB::table('clientes')->select('id','nombre','apellido')->orderBy('nombre')->get();
        $productos = DB::table('producto')->select('id_producto','nombre','precio_unitario')->where('habilitado',true)->orderBy('nombre')->get();
        return view('pedidos.edit', compact('pedido','clientes','productos'));
    }

    public function update(UpdatePedidoRequest $request, Pedido $pedido)
    {
        if (in_array($pedido->estado, ['entregado','anulado'])) {
            return back()->withErrors('No se puede modificar un pedido '.$pedido->estado);
        }

        $data = $request->validated();

        DB::transaction(function () use ($pedido, $data) {
            $pedido->update([
                'id_cliente'    => $data['id_cliente'],
                'fecha_entrega' => $data['fecha_entrega'] ?? $pedido->fecha_entrega,
                'metodo_pago'   => $data['metodo_pago'] ?? $pedido->metodo_pago,
                'observaciones' => $data['observaciones'] ?? $pedido->observaciones,
            ]);

            // Reemplaza todos los detalles
            $pedido->detalles()->delete();

            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = (float)$item['cantidad'] * (float)$item['precio_unitario'];
                $total += $subtotal;

                DetallePedido::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'id_producto'     => $item['id_producto'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal'        => $subtotal,
                ]);
            }

            $pedido->update(['total_pedido' => $total]);
        });

        return redirect()->route('pedidos.edit', $pedido->id_pedido)
            ->with('success', 'Pedido actualizado correctamente.');
    }
}
