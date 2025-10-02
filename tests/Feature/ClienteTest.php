<?php

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('usuario puede ver lista de clientes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('clientes.index'));

    $response->assertStatus(200);
});

test('usuario puede crear cliente', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $clienteData = [
        'nombre' => 'Juan',
        'apellido' => 'Pérez',
        'ci_nit' => '12345678',
        'telefono' => '77712345',
        'email' => 'juan@example.com',
        'direccion' => 'Calle Falsa 123',
    ];

    $response = $this->post(route('clientes.store'), $clienteData);

    $response->assertRedirect(route('clientes.index'));
    $this->assertDatabaseHas('clientes', $clienteData);
});

test('usuario puede actualizar cliente', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cliente = Cliente::factory()->create(['id_usuario' => $user->id_usuario]);

    $updatedData = [
        'nombre' => 'Juan Carlos',
        'apellido' => 'Pérez',
        'ci_nit' => '12345678',
        'telefono' => '77712345',
        'email' => 'juancarlos@example.com',
        'direccion' => 'Calle Verdadera 456',
    ];

    $response = $this->put(route('clientes.update', $cliente), $updatedData);

    $response->assertRedirect(route('clientes.index'));
    $this->assertDatabaseHas('clientes', $updatedData);
});

test('usuario puede eliminar cliente', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cliente = Cliente::factory()->create(['id_usuario' => $user->id_usuario]);

    $response = $this->delete(route('clientes.destroy', $cliente));

    $response->assertRedirect(route('clientes.index'));
    $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
});