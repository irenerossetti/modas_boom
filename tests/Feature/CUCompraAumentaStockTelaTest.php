<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Proveedor;
use App\Models\CompraInsumo;
use App\Models\Tela;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('compra vinculada a tela aumenta su stock', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $proveedor = Proveedor::create(['nombre' => 'Proveedor C']);
    $tela = Tela::create(['nombre' => 'Algodón', 'stock' => 50, 'unidad' => 'm', 'stock_minimo' => 10]);

    $response = $this->actingAs($admin)->post(route('compras.store'), [
        'proveedor_id' => $proveedor->id,
        'descripcion' => 'Compra de tela',
        'monto' => 200.00,
        'tela_id' => $tela->id,
        'cantidad' => 20
    ]);

    $response->assertRedirect(route('compras.index'));
    $this->assertDatabaseHas('compras_insumos', ['descripcion' => 'Compra de tela', 'tela_id' => $tela->id]);

    $this->assertDatabaseHas('telas', ['id' => $tela->id, 'stock' => 70.00]);
});
