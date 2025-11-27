<?php

use App\Models\Pago;
use App\Models\User;
use App\Models\Rol;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('en la lista de clientes aparece el boton Pagos y la deuda se actualiza tras anular un pago', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();
    // crear dos pedidos con total 100 y 200 => totalPed = 300
    $pedido1 = Pedido::create(['id_cliente' => $cliente->id, 'estado' => 'En proceso', 'total' => 100]);
    $pedido2 = Pedido::create(['id_cliente' => $cliente->id, 'estado' => 'En proceso', 'total' => 200]);
    // crear pago de 100 y 50 -> total pagado 150 -> deuda 150.00
    $p1 = Pago::create(['id_pedido' => $pedido1->id_pedido, 'id_cliente' => $cliente->id, 'monto' => 100, 'registrado_por' => $admin->id_usuario]);
    $p2 = Pago::create(['id_pedido' => $pedido2->id_pedido, 'id_cliente' => $cliente->id, 'monto' => 50, 'registrado_por' => $admin->id_usuario]);

    $response = $this->actingAs($admin)->get(route('clientes.index'));
    $response->assertStatus(200);
    // Ver el boton Pagos junto al cliente
    $response->assertSee('Pagos');
    // La deuda visible en la lista debe ser 150.00
    $response->assertSee('Bs. 150.00');

    // Anulamos el segundo pago -> deuda aumenta en 50 => 200.00
    \Illuminate\Support\Facades\DB::update('UPDATE "pago" SET "anulado" = true WHERE "id" = ?', [$p2->id]);

    $response2 = $this->actingAs($admin)->get(route('clientes.index'));
    $response2->assertStatus(200);
    $response2->assertSee('Bs. 200.00');
});
