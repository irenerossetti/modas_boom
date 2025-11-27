<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Proveedor;
use App\Models\CompraInsumo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede consultar historial de compras por proveedor', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $proveedor = Proveedor::create(['nombre' => 'Proveedor B']);
    CompraInsumo::create(['proveedor_id' => $proveedor->id, 'descripcion' => 'Compra 1', 'monto' => 100.00, 'fecha_compra' => now()]);
    CompraInsumo::create(['proveedor_id' => $proveedor->id, 'descripcion' => 'Compra 2', 'monto' => 200.00, 'fecha_compra' => now()]);

    $response = $this->actingAs($admin)->get(route('compras.historial.proveedor', $proveedor->id));
    $response->assertOk();
    $response->assertSee('Proveedor B');
    $response->assertSee('Compra 1');
    $response->assertSee('Compra 2');
});
