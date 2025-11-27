<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Tela;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede consumir stock de una tela tras producción y actualizar stock', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $tela = Tela::create(['nombre' => 'Algodón', 'stock' => 100, 'unidad' => 'm', 'stock_minimo' => 10]);

    $response = $this->actingAs($admin)->post(route('telas.consumir', $tela->id), ['cantidad' => 20]);
    $response->assertRedirect();
    $this->assertDatabaseHas('telas', ['id' => $tela->id, 'stock' => 80]);
});
