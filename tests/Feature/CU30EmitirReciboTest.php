<?php

use App\Models\Pago;
use App\Models\User;
use App\Models\Rol;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('se puede emitir un recibo digital para un pago', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();
    $pedido = Pedido::create(['id_cliente'=>$cliente->id, 'estado'=>'En proceso', 'total'=>100]);
    $pago = Pago::create(['id_pedido'=>$pedido->id_pedido, 'id_cliente'=>$cliente->id, 'monto' => 50, 'registrado_por' => $admin->id_usuario]);

    $response = $this->actingAs($admin)->get(route('pagos.recibo', $pago->id));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
    // Ensure the response is a valid PDF - starts with %PDF
    $content = $response->getContent();
    $this->assertStringStartsWith('%PDF', $content);
    // Ensure the font name is embedded somewhere in the PDF bytes (DejaVu font family)
    $this->assertStringContainsString('DejaVu', $content);
    // Note: text content checks on binary PDFs can be memory-heavy. The important part is the response is a valid PDF and embeds the font.
});
