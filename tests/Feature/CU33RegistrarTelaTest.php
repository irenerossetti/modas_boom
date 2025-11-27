<?php

use App\Models\User;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede registrar una tela nueva', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $response = $this->actingAs($admin)->post(route('telas.store'), [
        'nombre' => 'Algodón premium',
        'descripcion' => 'Tela muy buena para camisetas',
        'stock' => 50,
        'unidad' => 'm',
        'stock_minimo' => 10,
    ]);
    $response->assertRedirect(route('telas.index'));
    $this->assertDatabaseHas('telas', ['nombre' => 'Algodón premium', 'stock' => 50]);
});
