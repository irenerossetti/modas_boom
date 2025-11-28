<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Prenda;

class PrendaActivoInsertTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure that passing activo as integer 1 does not cause Postgres boolean mismatch
     * and is stored/casted as a boolean attribute on the model.
     */
    public function test_activo_integer_is_saved_as_boolean()
    {
        $prenda = Prenda::create([
            'nombre' => 'Test Insert',
            'categoria' => 'CatTest',
            'precio' => 12.00,
            'stock' => 10,
            'activo' => 1,
        ]);

        $this->assertTrue((bool)$prenda->activo);
        $fresh = $prenda->fresh();
        $this->assertTrue((bool)$fresh->activo);
    }
}
