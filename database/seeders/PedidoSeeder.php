<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use App\Models\DetallePedido;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $clientes  = DB::table('clientes')->pluck('id');
        $productos = DB::table('producto')->select('id_producto','precio_unitario')->get();

        if ($clientes->isEmpty() || $productos->isEmpty()) {
            $this->command->warn('No hay clientes o productos para crear pedidos.');
            return;
        }

        $clienteIds = $clientes->take(3)->values();
        $prodById   = $productos->keyBy('id_producto');

        $pedidosData = [
            [
                'id_cliente'    => $clienteIds[0],
                'fecha_pedido'  => now()->toDateString(),
                'fecha_entrega' => now()->addDays(7)->toDateString(),
                'metodo_pago'   => 'Efectivo',
                'observaciones' => 'Entrega en tienda',
                'estado'        => 'registrado',
                'items'         => [
                    ['id_producto' => $productos[0]->id_producto, 'cantidad'=>1,  'precio_unitario'=>$prodById[$productos[0]->id_producto]->precio_unitario],
                    ['id_producto' => $productos[1]->id_producto, 'cantidad'=>2,  'precio_unitario'=>$prodById[$productos[1]->id_producto]->precio_unitario],
                ],
            ],
            [
                'id_cliente'    => $clienteIds[1] ?? $clienteIds[0],
                'fecha_pedido'  => now()->subDay()->toDateString(),
                'fecha_entrega' => now()->addDays(5)->toDateString(),
                'metodo_pago'   => 'Tarjeta',
                'observaciones' => 'Enviar a domicilio',
                'estado'        => 'registrado',
                'items'         => [
                    ['id_producto' => $productos[2]->id_producto, 'cantidad'=>3,  'precio_unitario'=>$prodById[$productos[2]->id_producto]->precio_unitario],
                ],
            ],
            [
                'id_cliente'    => $clienteIds[2] ?? $clienteIds[0],
                'fecha_pedido'  => now()->subDays(2)->toDateString(),
                'fecha_entrega' => now()->addDays(10)->toDateString(),
                'metodo_pago'   => 'Transferencia',
                'observaciones' => null,
                'estado'        => 'registrado',
                'items'         => [
                    ['id_producto' => $productos[3]->id_producto, 'cantidad'=>1.5, 'precio_unitario'=>$prodById[$productos[3]->id_producto]->precio_unitario],
                    ['id_producto' => $productos[4]->id_producto, 'cantidad'=>1,   'precio_unitario'=>$prodById[$productos[4]->id_producto]->precio_unitario],
                ],
            ],
        ];

        foreach ($pedidosData as $pd) {
            DB::transaction(function () use ($pd) {
                $pedido = Pedido::create([
                    'id_cliente'    => $pd['id_cliente'],
                    'fecha_pedido'  => $pd['fecha_pedido'],
                    'fecha_entrega' => $pd['fecha_entrega'],
                    'metodo_pago'   => $pd['metodo_pago'],
                    'observaciones' => $pd['observaciones'],
                    'estado'        => $pd['estado'],
                    'total_pedido'  => 0,
                ]);

                $total = 0;
                foreach ($pd['items'] as $it) {
                    $subtotal = (float)$it['cantidad'] * (float)$it['precio_unitario'];
                    $total += $subtotal;

                    DetallePedido::create([
                        'id_pedido'       => $pedido->id_pedido,
                        'id_producto'     => $it['id_producto'],
                        'cantidad'        => $it['cantidad'],
                        'precio_unitario' => $it['precio_unitario'],
                        'subtotal'        => $subtotal,
                    ]);
                }

                $pedido->update(['total_pedido' => $total]);
            });
        }
    }
}
