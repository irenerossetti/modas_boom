<?php

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede confirmar recepcion de pedido terminado', function () {
    $admin = User::factory()->create(['id_rol' => 1]);

    $cliente = Cliente::factory()->create();

    $pedido = Pedido::create([
        'id_cliente' => $cliente->id,
        'estado' => 'Terminado',
        'total' => 120.00,
    ]);

    $response = $this->actingAs($admin)->post(route('pedidos.confirmar-recepcion', $pedido->id_pedido));

    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
    $this->assertDatabaseHas('pedido', ['id_pedido' => $pedido->id_pedido, 'estado' => 'Entregado']);
});

test('cliente propietario puede confirmar recepcion de su pedido terminado', function () {
    $user = User::factory()->create(['id_rol' => 3]);
    $cliente = Cliente::factory()->create(['id_usuario' => $user->id_usuario]);

    $pedido = Pedido::create([
        'id_cliente' => $cliente->id,
        'estado' => 'Terminado',
        'total' => 50.00,
    ]);

    $response = $this->actingAs($user)->post(route('pedidos.confirmar-recepcion', $pedido->id_pedido));

    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
    $this->assertDatabaseHas('pedido', ['id_pedido' => $pedido->id_pedido, 'estado' => 'Entregado']);
});

test('cliente no propietario no puede confirmar recepcion de otro pedido', function () {
    $user = User::factory()->create(['id_rol' => 3]);
    $otro = Cliente::factory()->create();

    $pedido = Pedido::create([
        'id_cliente' => $otro->id,
        'estado' => 'Terminado',
    ]);

    $response = $this->actingAs($user)->post(route('pedidos.confirmar-recepcion', $pedido->id_pedido));

    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
    $this->assertDatabaseHas('pedido', ['id_pedido' => $pedido->id_pedido, 'estado' => 'Terminado']);
});