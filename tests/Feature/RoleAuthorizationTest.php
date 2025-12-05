<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('usuario no autenticado es redirigido al login', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('admin puede acceder a rutas administrativas', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    
    $response = $this->actingAs($admin)->get(route('users.index'));
    $response->assertOk();
});

test('empleado no puede acceder a gestion de usuarios', function () {
    $empleado = User::factory()->create(['id_rol' => 2]);
    
    $response = $this->actingAs($empleado)->get(route('users.index'));
    $response->assertStatus(403); // O el código que uses para forbidden
});

test('cliente no puede acceder a rutas administrativas', function () {
    $cliente = User::factory()->create(['id_rol' => 3]);
    
    $response = $this->actingAs($cliente)->get(route('users.index'));
    $response->assertStatus(403);
});

test('usuario deshabilitado no puede autenticarse', function () {
    $user = User::factory()->create([
        'habilitado' => false,
        'password' => bcrypt('password')
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});
