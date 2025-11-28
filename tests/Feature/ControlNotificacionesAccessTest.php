<?php

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede acceder a la vista control de notificaciones', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $response = $this->actingAs($admin)->get(route('control-notificaciones'));
    $response->assertOk();
    $response->assertSee('Control de Notificaciones');
});
