<?php

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Prenda;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede registrar devolucion de prenda y se incrementa stock', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();

    $prenda = Prenda::create(['nombre' => 'Prenda A', 'categoria' => 'Cat1', 'precio' => 10.00, 'stock' => 5, 'activo' => true]);

    $pedido = Pedido::create([
        'id_cliente' => $cliente->id,
        'estado' => 'Terminado',
        'total' => 100,
    ]);

    // Adjuntar prenda al pedido (cantidad en unidades - por ejemplo 12 unidades = 1 docena)
    $pedido->prendas()->attach($prenda->id, ['cantidad' => 12, 'precio_unitario' => 50]);

    // Reduce stock inicialmente como si hubieran sido descontadas 2 docenas (2 * 12 unidades)
    $prenda->descontarStock(2);
    $this->assertDatabaseHas('prendas', ['id' => $prenda->id, 'stock' => 3]);

    $response = $this->actingAs($admin)->post(route('pedidos.devoluciones.store', $pedido->id_pedido), [
        'prenda_id' => $prenda->id,
        'cantidad' => 12,
        'motivo' => 'Defectuosa'
    ]);

    $response->assertRedirect(route('pedidos.show', $pedido->id_pedido));
    $this->assertDatabaseHas('devolucion_prenda', ['id_pedido' => $pedido->id_pedido, 'id_prenda' => $prenda->id, 'cantidad' => 1]);

    // Stock debe aumentar en 1
    $prenda->refresh();
    $this->assertEquals(4, $prenda->stock);

    // Verificar que se registró la acción en la bitácora con módulo PEDIDOS
    $this->assertDatabaseHas('bitacora', [
        'modulo' => 'PEDIDOS',
    ]);

    // Verificar que exista un registro en bitácora del modulo PEDIDOS con referencia a devolución
    $this->assertTrue(\App\Models\Bitacora::where('modulo','PEDIDOS')->where('descripcion','like','%devoluci%')->exists());

    // Verificar que la vista del pedido muestra el total devuelto
    $response2 = $this->actingAs($admin)->get(route('pedidos.show', $pedido->id_pedido));
    $response2->assertStatus(200);
    $response2->assertSee('Total devuelto');
    $response2->assertSee('12 unidades');

    // Ver historial del pedido - debe contener la entrada de devolucion
    $histPage = $this->actingAs($admin)->get(route('pedidos.historial', $pedido->id_pedido));
    $histPage->assertStatus(200);
    $histPage->assertSee('devolució');
});
