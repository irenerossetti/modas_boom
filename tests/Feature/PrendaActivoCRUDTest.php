<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Prenda;
use App\Models\User;

class PrendaActivoCRUDTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_prenda_inactive_when_checkbox_unchecked()
    {
        $admin = User::factory()->create(['id_rol' => 1]);

        $response = $this->actingAs($admin)->post(route('prendas.store'), [
            'nombre' => 'Test Create Inactive',
            'categoria' => 'CatTest',
            'precio' => 10.00,
            'stock' => 5,
            // omit 'activo' to simulate unchecked checkbox
        ]);

        $response->assertRedirect(route('prendas.index'));
        $this->assertDatabaseHas('prendas', [
            'nombre' => 'Test Create Inactive',
            'activo' => false,
        ]);
    }

    public function test_update_prenda_unchecked_becomes_inactive()
    {
        $admin = User::factory()->create(['id_rol' => 1]);

        $prenda = Prenda::create([
            'nombre' => 'Test Update Active',
            'categoria' => 'CatTest',
            'precio' => 10.00,
            'stock' => 5,
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)
                         ->put(route('prendas.update', $prenda), [
                             'nombre' => $prenda->nombre,
                             'categoria' => $prenda->categoria,
                             'precio' => $prenda->precio,
                             'stock' => $prenda->stock,
                             // omit 'activo' to uncheck
                         ]);

        $response->assertRedirect(route('prendas.index'));
        $this->assertDatabaseHas('prendas', [
            'id' => $prenda->id,
            'activo' => false,
        ]);
    }

    public function test_update_prenda_checked_becomes_active()
    {
        $admin = User::factory()->create(['id_rol' => 1]);

        $prenda = Prenda::create([
            'nombre' => 'Test Update Inactive',
            'categoria' => 'CatTest',
            'precio' => 10.00,
            'stock' => 5,
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)
                         ->put(route('prendas.update', $prenda), [
                             'nombre' => $prenda->nombre,
                             'categoria' => $prenda->categoria,
                             'precio' => $prenda->precio,
                             'stock' => $prenda->stock,
                             'activo' => '1',
                         ]);

        $response->assertRedirect(route('prendas.index'));
        $this->assertDatabaseHas('prendas', [
            'id' => $prenda->id,
            'activo' => true,
        ]);
    }

    // Not using createAdmin helper — tests create admin user inline
}
