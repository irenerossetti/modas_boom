<?php

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede registrar avance de produccion', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();

    $pedido = Pedido::create([
        'id_cliente' => $cliente->id,
        'estado' => 'Asignado',
        'total' => 200.00,
    ]);

    $response = $this->actingAs($admin)->post(route('pedidos.procesar-avance', $pedido->id_pedido), [
        'etapa' => 'Corte',
        'porcentaje_avance' => 20,
        'descripcion' => 'Corte realizado',
        'observaciones' => 'Sin novedades'
    ]);

    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
    $this->assertDatabaseHas('avance_produccion', ['id_pedido' => $pedido->id_pedido, 'etapa' => 'Corte', 'porcentaje_avance' => 20]);

    // Pedido debe cambiar a En producción
    $this->assertDatabaseHas('pedido', ['id_pedido' => $pedido->id_pedido, 'estado' => 'En producción']);
});

test('empleado no puede registrar avance cuando solo admin deberia poder', function () {
    $empleado = User::factory()->create(['id_rol' => 2]);
    $cliente = Cliente::factory()->create();

    $pedido = Pedido::create([
        'id_cliente' => $cliente->id,
        'estado' => 'Asignado',
        'total' => 150.00,
    ]);

    $response = $this->actingAs($empleado)->post(route('pedidos.procesar-avance', $pedido->id_pedido), [
        'etapa' => 'Corte',
        'porcentaje_avance' => 10,
        'descripcion' => 'Prueba empleado'
    ]);

    // Debe redirigir con mensaje de error
    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
    $this->assertDatabaseMissing('avance_produccion', ['id_pedido' => $pedido->id_pedido, 'descripcion' => 'Prueba empleado']);
});
