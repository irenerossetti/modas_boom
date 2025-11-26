<?php

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

test('admin puede ver y procesar reprogramacion de entrega', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    auth()->login($admin);

    $cliente = Cliente::factory()->create();

    $pedido = Pedido::create([
        'id_cliente' => $cliente->id,
        'estado' => 'En proceso',
        'total' => 100.00,
    ]);

    // GET form
    $response = $this->actingAs($admin)->get(route('pedidos.reprogramar-entrega', $pedido->id_pedido));
    $response->assertStatus(200);

    // POST new date
    $nuevaFecha = Carbon::now()->addDays(2)->format('Y-m-d');

    $response = $this->actingAs($admin)->post(route('pedidos.procesar-reprogramacion', $pedido->id_pedido), [
        'nueva_fecha_entrega' => $nuevaFecha,
        'motivo_reprogramacion' => 'Prueba por admin'
    ]);

    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
    $this->assertDatabaseHas('pedido', ['id_pedido' => $pedido->id_pedido, 'observaciones_entrega' => 'Prueba por admin']);
});

test('cliente propietario puede reprogramar su pedido', function () {
    $user = User::factory()->create(['id_rol' => 3]);
    $cliente = Cliente::factory()->create(['id_usuario' => $user->id_usuario]);

    $pedido = Pedido::create([
        'id_cliente' => $cliente->id,
        'estado' => 'En proceso',
        'total' => 50.00,
    ]);

    $nuevaFecha = Carbon::now()->addDays(3)->format('Y-m-d');

    $response = $this->actingAs($user)->post(route('pedidos.procesar-reprogramacion', $pedido->id_pedido), [
        'nueva_fecha_entrega' => $nuevaFecha,
        'motivo_reprogramacion' => 'Cambio por cliente'
    ]);

    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
    $this->assertDatabaseHas('pedido', ['id_pedido' => $pedido->id_pedido, 'observaciones_entrega' => 'Cambio por cliente']);
});

test('cliente no propietario no puede reprogramar pedido de otro', function () {
    $user = User::factory()->create(['id_rol' => 3]);
    $otroCliente = Cliente::factory()->create();

    $pedido = Pedido::create([
        'id_cliente' => $otroCliente->id,
        'estado' => 'En proceso',
    ]);

    $response = $this->actingAs($user)->get(route('pedidos.reprogramar-entrega', $pedido->id_pedido));

    // controlador redirige con mensaje de error
    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
});
