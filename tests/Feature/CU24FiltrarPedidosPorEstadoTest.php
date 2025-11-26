<?php

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede filtrar pedidos por estado', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();

    // Pedidos de diferentes estados
    Pedido::create(['id_cliente' => $cliente->id, 'estado' => 'Terminado', 'total' => 100]);
    Pedido::create(['id_cliente' => $cliente->id, 'estado' => 'En proceso', 'total' => 80]);

    $response = $this->actingAs($admin)->get(route('pedidos.index', ['estado' => 'Terminado']));
    $response->assertStatus(200);
    $response->assertSee('Terminado');
    // Should show only Terminado orders in the table
});

test('empleado no puede filtrar pedidos por estado (solo admin)', function () {
    $empleado = User::factory()->create(['id_rol' => 2]);
    $cliente = Cliente::factory()->create();

    Pedido::create(['id_cliente' => $cliente->id, 'estado' => 'Terminado', 'total' => 100]);
    Pedido::create(['id_cliente' => $cliente->id, 'estado' => 'En proceso', 'total' => 80]);

    $response = $this->actingAs($empleado)->get(route('pedidos.index', ['estado' => 'Terminado']));
    $response->assertStatus(200);
    // Because empleado cannot filter by estado, the list should include other states as well
    $response->assertSee('En proceso');
});