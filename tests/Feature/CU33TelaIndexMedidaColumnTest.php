<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Tela;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('telas index shows single medida column and numeric stock values without unit duplication', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Tela::create([
        'nombre' => 'Algodón Premium',
        'descripcion' => 'Excelente',
        'stock' => 100,
        'unidad' => 'm',
        'stock_minimo' => 10,
    ]);

    $response = $this->actingAs($admin)->get(route('telas.index'));
    $response->assertOk();
    $response->assertSee('Medida');
    $response->assertSee('Registrar Tela');
    $response->assertSee('100.00');
    $response->assertSee('10.00');
    $response->assertSee('m');
    // Ensure the unit is not duplicated next to the stock numbers
    $response->assertDontSee('100.00 m');
    $response->assertDontSee('10.00 m');
});
