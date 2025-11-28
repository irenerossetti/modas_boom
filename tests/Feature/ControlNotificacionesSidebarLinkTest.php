<?php

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Administrador ve enlace de control de notificaciones en el sidebar', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Control de Notificaciones');
});
