<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Proveedor;
use App\Models\CompraInsumo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede auditar movimientos de inventario de la ultima semana', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    $proveedor = Proveedor::create(['nombre' => 'Proveedor C']);
    // compra de esta semana
    CompraInsumo::create(['proveedor_id' => $proveedor->id, 'descripcion' => 'Compra reciente', 'monto' => 123.45, 'fecha_compra' => now()]);
    // compra hace más de dos semanas
    CompraInsumo::create(['proveedor_id' => $proveedor->id, 'descripcion' => 'Compra vieja', 'monto' => 99.99, 'fecha_compra' => now()->subWeeks(3)]);

    // Registrar un bitacora dentro de última semana
    \App\Services\BitacoraService::class;
    $bitacoraService = new \App\Services\BitacoraService();
    $bitacoraService->registrarActividad('UPDATE', 'INVENTARIO', 'Movimiento reciente de inventario', null, []);

    // Registrar un bitacora viejo: usamos insert para fijar created_at
    \Illuminate\Support\Facades\DB::table('bitacora')->insert([
        'id_usuario' => $admin->id_usuario,
        'accion' => 'CREATE',
        'modulo' => 'INVENTARIO',
        'descripcion' => 'Movimiento viejo',
        'datos_anteriores' => null,
        'datos_nuevos' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => null,
        'created_at' => now()->subWeeks(3),
        'updated_at' => now()->subWeeks(3),
    ]);

    $response = $this->actingAs($admin)->get(route('compras.auditar.ultima-semana'));

    $response->assertOk();
    $response->assertSee('Compras');
    $response->assertSee('Compra reciente');
    $response->assertDontSee('Compra vieja');
    $response->assertSee('Movimiento reciente de inventario');
    $response->assertDontSee('Movimiento viejo');
});
