<?php

use App\Models\Pago;
use App\Models\User;
use App\Models\Rol;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('mostrar pagos en la vista del pedido', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();
    $p = \App\Models\Prenda::create(['nombre'=>'P', 'categoria'=>'C', 'precio'=>10, 'stock'=>10, 'activo'=>true]);
    $pedido = Pedido::create(['id_cliente'=>$cliente->id, 'estado'=>'En proceso', 'total'=>100]);
    // Adjuntar prenda al pedido para que el resumen muestre el Total pagado
    $pedido->prendas()->attach($p->id, ['cantidad' => 12, 'precio_unitario' => 10]);
    $pago = Pago::create(['id_pedido'=>$pedido->id_pedido, 'id_cliente'=>$cliente->id, 'monto' => 50, 'registrado_por' => $admin->id_usuario]);

    $response = $this->actingAs($admin)->get(route('pedidos.show', $pedido->id_pedido));
    $response->assertStatus(200);
    $response->assertSee('Pagos del Pedido');
    $response->assertSee('Recibo');
    $response->assertSee('Total pagado');
});
