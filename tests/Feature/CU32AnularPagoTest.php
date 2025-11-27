<?php

use App\Models\Pago;
use App\Models\User;
use App\Models\Rol;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Prenda;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede anular un pago y se registra en bitacora', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();
    $prenda = Prenda::create(['nombre'=>'P', 'categoria'=>'C', 'precio'=>10, 'stock'=>10, 'activo'=>true]);
    $pedido = Pedido::create(['id_cliente'=>$cliente->id, 'estado'=>'En proceso', 'total'=>100]);
    $pedido->prendas()->attach($prenda->id, ['cantidad'=>12, 'precio_unitario'=>10]);

    $pago = Pago::create(['id_pedido'=>$pedido->id_pedido, 'id_cliente'=>$cliente->id, 'monto' => 50.00, 'registrado_por' => $admin->id_usuario]);

    $response = $this->actingAs($admin)->post(route('pagos.anular', $pago->id), [
        'motivo' => 'Pago duplicado'
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('pago', ['id' => $pago->id, 'anulado' => true, 'anulado_motivo' => 'Pago duplicado']);

    // Verificar que existan bitacora 'PAGOS' y 'PEDIDOS' para la anulacion
    $this->assertDatabaseHas('bitacora', ['modulo' => 'PAGOS']);
    $this->assertDatabaseHas('bitacora', ['modulo' => 'PEDIDOS']);
});
