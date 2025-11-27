<?php

use App\Models\User;
use App\Models\Rol;
use App\Models\Tela;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\BitacoraService;

uses(RefreshDatabase::class);

test('al consumir stock por producción y quedar bajo el minimo, se genera una alerta en la bitácora', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);
    $tela = Tela::create(['nombre' => 'Lino', 'stock' => 15, 'unidad' => 'm', 'stock_minimo' => 10]);

    // consumir 6 metros deja stock en 9 => por debajo del mínimo (10)
    $response = $this->actingAs($admin)->post(route('telas.consumir', $tela->id), ['cantidad' => 6]);
    $response->assertRedirect();

    // Verificar que hay bitacora con ALERTA e INVERTARIO o similar
    $registro = \App\Models\Bitacora::where('modulo', 'INVENTARIO')->where('accion', 'ALERTA')->where('descripcion', 'like', "%Stock bajo para la tela%")->first();
    $this->assertNotNull($registro);
});
