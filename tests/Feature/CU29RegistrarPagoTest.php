<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Cliente;
use App\Models\Prenda;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede registrar pago para un pedido', function () {
    // Asegurar que exista el rol administrador para la FK
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();
    $p = Prenda::create(['nombre'=>'P', 'categoria'=>'C', 'precio'=>10, 'stock'=>10, 'activo'=>true]);
    $pedido = Pedido::create(['id_cliente'=>$cliente->id, 'estado'=>'En proceso', 'total' => 100]);
    $response = $this->actingAs($admin)->post(route('pedidos.pagos.store', $pedido->id_pedido), [
        'monto' => 50,
        'metodo' => 'Efectivo',
        'referencia' => '1234'
    ]);
    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
    $this->assertDatabaseHas('pago', ['id_pedido' => $pedido->id_pedido, 'monto' => 50]);
    $this->assertDatabaseHas('bitacora', ['modulo' => 'PAGOS']);
});
