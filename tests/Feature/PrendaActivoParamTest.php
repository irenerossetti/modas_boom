<?php

use App\Models\Prenda;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('filtrado por activo acepta 1/0 en la query y devuelve resultados booleanos', function () {
    $admin = User::factory()->create(['id_rol' => 1]);

    // Crear prendas
    $activa = Prenda::create(['nombre' => 'Activa', 'categoria' => 'Cat1', 'precio' => 10, 'stock' => 5, 'activo' => true]);
    $inactiva = Prenda::create(['nombre' => 'Inactiva', 'categoria' => 'Cat1', 'precio' => 10, 'stock' => 2, 'activo' => false]);

    // Acceder con activo=1 debe mostrar solo la prenda activa
    $response = $this->actingAs($admin)->get(route('prendas.index', ['activo' => '1']));
    $response->assertStatus(200);
    $response->assertSee('Activa')->assertDontSee('Inactiva');

    // Acceder con activo=0 debe mostrar solo la prenda inactiva
    $response = $this->actingAs($admin)->get(route('prendas.index', ['activo' => '0']));
    $response->assertStatus(200);
    $response->assertSee('Inactiva')->assertDontSee('Activa');
});
