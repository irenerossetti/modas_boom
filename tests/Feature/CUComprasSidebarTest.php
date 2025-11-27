<?php

use App\Models\User;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin sidebar contains Compras link at top level and not nested under Inventario', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertOk();
    $content = $response->getContent();
    $this->assertStringContainsString('Compras', $content);
    $this->assertStringContainsString(route('compras.index'), $content);

    // Ensure 'Compras' is not inside the nested inventory block (ml-6)
    $posCompras = strpos($content, route('compras.index'));
    $posInventoryDiv = strpos($content, '<div class="ml-6 mt-1">');
    $this->assertFalse($posCompras > $posInventoryDiv && $posCompras < strpos($content, '</div>', $posInventoryDiv));
});
