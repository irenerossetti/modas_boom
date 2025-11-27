<?php

use App\Models\Pago;
use App\Models\User;
use App\Models\Rol;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede ver pagos de un cliente y total pagado excluye anulado', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();
    $pedido = Pedido::create(['id_cliente' => $cliente->id, 'estado' => 'En proceso', 'total' => 100]);
    $p1 = Pago::create(['id_pedido' => $pedido->id_pedido, 'id_cliente' => $cliente->id, 'monto' => 60, 'registrado_por' => $admin->id_usuario]);
    $p2 = Pago::create(['id_pedido' => $pedido->id_pedido, 'id_cliente' => $cliente->id, 'monto' => 40, 'registrado_por' => $admin->id_usuario]);
    // mark second as anulado
    \Illuminate\Support\Facades\DB::update('UPDATE "pago" SET "anulado" = true WHERE "id" = ?', [$p2->id]);
    $response = $this->actingAs($admin)->get(route('clientes.pagos', $cliente->id));
    $response->assertStatus(200);
    $response->assertSee('Pagos del Cliente');
    $response->assertSee('Total pagado');
    $response->assertSee('Deuda actual');
    // Total should be 60 only
    $response->assertSee('Bs. 60.00');
    // Should show both pagos and indicate ANULADO for the second
    $response->assertSee('ANULADO');
});
