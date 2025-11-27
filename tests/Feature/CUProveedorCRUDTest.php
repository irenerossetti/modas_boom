<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede CRUD proveedores', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    // Create
    $response = $this->actingAs($admin)->post(route('proveedores.store'), [
        'nombre' => 'Proveedor Test',
        'contacto' => 'Ana',
        'telefono' => '555-2222',
        'email' => 'prueba@proveedor.com'
    ]);
    $response->assertRedirect(route('proveedores.index'));
    $this->assertDatabaseHas('proveedores', ['nombre' => 'Proveedor Test']);

    $proveedor = Proveedor::where('nombre', 'Proveedor Test')->first();
    // Edit
    $response = $this->actingAs($admin)->put(route('proveedores.update', $proveedor->id), [
        'nombre' => 'Proveedor Test Mod',
        'contacto' => 'Ana Mod',
        'telefono' => '555-3333',
        'email' => 'actualizado@proveedor.com'
    ]);
    $response->assertRedirect(route('proveedores.index'));
    $this->assertDatabaseHas('proveedores', ['nombre' => 'Proveedor Test Mod']);

    // Delete
    $response = $this->actingAs($admin)->delete(route('proveedores.destroy', $proveedor->id));
    $response->assertRedirect(route('proveedores.index'));
    $this->assertDatabaseMissing('proveedores', ['nombre' => 'Proveedor Test Mod']);
});
