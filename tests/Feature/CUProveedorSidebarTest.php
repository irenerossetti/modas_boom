<?php

use App\Models\User;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin sidebar contains Proveedores link', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertOk();
    $content = $response->getContent();
    $this->assertStringContainsString('Proveedores', $content);
    $this->assertStringContainsString(route('proveedores.index'), $content);

    // Verify it's not in the nested inventory block (inside ml-6)
    $posProveedor = strpos($content, route('proveedores.index'));
    $posInventoryDiv = strpos($content, '<div class="ml-6 mt-1">');
    // If the proveedor link is in the nested block, it would appear after the inventory div opening
    $this->assertFalse($posProveedor > $posInventoryDiv && $posProveedor < strpos($content, '</div>', $posInventoryDiv) );
    // also check that the route exists and page loads
    $response = $this->actingAs($admin)->get(route('proveedores.index'));
    $response->assertOk();
});
