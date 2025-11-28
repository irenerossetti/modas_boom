<?php

use App\Models\Rol;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('clientes/info/json devuelve mapping para el cliente autenticado', function () {
    // Crear rol Cliente
    Rol::create(['nombre' => 'Cliente', 'habilitado' => true]);
    $user = User::factory()->create(['id_rol' => 3]);
    // Crear cliente asociado
    $cliente = Cliente::create([
        'id_usuario' => $user->id_usuario,
        'nombre' => 'Darwin',
        'apellido' => 'Vigabriel',
        'ci_nit' => '12345678',
        'telefono' => '+59178507750',
        'email' => 'darwin@example.com',
        'direccion' => 'Santos Dumont'
    ]);

    $this->actingAs($user);
    $response = $this->get('/clientes/info/json');
    $response->assertOk();
    $json = $response->json();
    $this->assertArrayHasKey('59178507750', $json);
    $this->assertEquals('Darwin Vigabriel', $json['59178507750']['nombre_completo']);
});

test('clientes/info/json usa telefono del usuario si no existe registro Cliente', function () {
    Rol::create(['nombre' => 'Cliente', 'habilitado' => true]);
    $user = User::factory()->create(['id_rol' => 3, 'telefono' => '+59178507750', 'nombre' => 'SinRegistro NoCliente']);
    // Note: not creating Cliente record here

    $this->actingAs($user);
    $response = $this->get('/clientes/info/json');
    $response->assertOk();
    $json = $response->json();
    $this->assertArrayHasKey('59178507750', $json);
    $this->assertArrayHasKey('78507750', $json);
    $this->assertEquals('SinRegistro NoCliente', $json['59178507750']['nombre_completo']);
    $this->assertEquals('SinRegistro NoCliente', $json['78507750']['nombre_completo']);
});

test('admin clientes/json devuelve ambos formatos para cada telefono', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::create([
        'id_usuario' => $admin->id_usuario,
        'nombre' => 'Test',
        'apellido' => 'Cliente',
        'telefono' => '+59178507750',
        'ci_nit' => '111111',
        'email' => 'test.cliente@example.com',
        'direccion' => 'Address'
    ]);
    $this->actingAs($admin);
    $res = $this->get('/admin/clientes/json');
    $res->assertOk();
    $map = $res->json();
    $this->assertArrayHasKey('78507750', $map);
    $this->assertArrayHasKey('59178507750', $map);
});
