<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Prenda;
use App\Models\Tela;
use App\Models\Pedido;
use App\Models\CompraInsumo;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede generar reporte en csv', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Prenda::create(['nombre' => 'Remera', 'descripcion' => 'Remera', 'precio' => 15.5, 'categoria' => 'Cat1']);
    Tela::create(['nombre' => 'Algodón', 'stock' => 100, 'unidad' => 'm', 'stock_minimo' => 10]);

    $proveedor = Proveedor::create(['nombre' => 'Proveedor Z']);
    CompraInsumo::create(['proveedor_id' => $proveedor->id, 'descripcion' => 'Compra test', 'monto' => 20, 'fecha_compra' => now()]);
    // Crear cliente para el pedido
    $cliente = \App\Models\Cliente::create(['nombre' => 'Cliente Test', 'apellido' => 'A', 'ci_nit' => '123', 'telefono' => '123', 'email' => 'cliente@example.com', 'direccion' => 'Calle 1']);
    Pedido::create(['id_cliente' => $cliente->id, 'total' => 200, 'created_at' => now(), 'estado' => 'completado']);

    $response = $this->actingAs($admin)->post(route('reportes.generate'), [
        'format' => 'csv',
        'desde' => now()->subDay()->format('Y-m-d'),
        'hasta' => now()->format('Y-m-d'),
        'sections' => ['productos','telas','ventas','compras']
    ]);

    $response->assertStatus(200);
    $contentType = $response->headers->get('content-type');
    $this->assertStringContainsString('text/csv', $contentType);

    // For streaming responses in tests, content may be unavailable. Verify headers and disposition
    $disposition = $response->headers->get('content-disposition');
    $this->assertStringContainsString('.csv', $disposition);
});
