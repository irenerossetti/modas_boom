<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede registrar compra de insumos a proveedor', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $proveedor = Proveedor::create(['nombre' => 'Proveedor A', 'contacto' => 'Juan', 'telefono' => '123', 'email' => 'proveedor@example.com']);

    $response = $this->actingAs($admin)->post(route('compras.store'), [
        'proveedor_id' => $proveedor->id,
        'descripcion' => 'Compra de botones y hilos',
        'monto' => 150.50,
        'fecha_compra' => now()->format('Y-m-d')
    ]);

    $response->assertRedirect(route('compras.index'));
    $this->assertDatabaseHas('compras_insumos', ['descripcion' => 'Compra de botones y hilos', 'monto' => 150.50]);

    $registro = \App\Models\Bitacora::where('modulo', 'INVENTARIO')->where('accion', 'CREATE')->where('descripcion', 'like', '%Compra registrada%')->first();
    $this->assertNotNull($registro);
});
