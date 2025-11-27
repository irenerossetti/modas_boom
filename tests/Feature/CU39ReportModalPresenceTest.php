<?php

use App\Models\User;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('el dashboard muestra el modal para generar reportes', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Generar Reporte');
    // modal markup
    // Modal fields (raw, do not escape HTML attributes)
    $response->assertSee('name="format"', false);
    $response->assertSee('name="desde"', false);
    $response->assertSee('name="hasta"', false);
});
