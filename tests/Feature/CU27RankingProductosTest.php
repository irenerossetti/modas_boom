<?php

use App\Models\User;
use App\Models\Cliente;
use App\Models\Prenda;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ranking de productos muestra orden correcto por unidades vendidas', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    $cliente = Cliente::factory()->create();

    $p1 = Prenda::create(['nombre' => 'P1', 'categoria' => 'Cat1', 'precio' => 10, 'stock' => 100, 'activo' => true]);
    $p2 = Prenda::create(['nombre' => 'P2', 'categoria' => 'Cat1', 'precio' => 15, 'stock' => 100, 'activo' => true]);

    // Pedido 1: P1 x10, P2 x5
    $pedido1 = Pedido::create(['id_cliente' => $cliente->id, 'estado' => 'Terminado', 'total' => 200]);
    $pedido1->prendas()->attach($p1->id, ['cantidad' => 10, 'precio_unitario' => 10]);
    $pedido1->prendas()->attach($p2->id, ['cantidad' => 5, 'precio_unitario' => 15]);

    // Pedido 2: P1 x2
    $pedido2 = Pedido::create(['id_cliente' => $cliente->id, 'estado' => 'Terminado', 'total' => 20]);
    $pedido2->prendas()->attach($p1->id, ['cantidad' => 2, 'precio_unitario' => 10]);

    $response = $this->actingAs($admin)->get(route('prendas.ranking'));
    $response->assertStatus(200);
    $content = $response->getContent();
    // Se espera que P1 (12 unidades) aparezca antes que P2 (5 unidades)
    $this->assertStringContainsString('P1', $content);
    $this->assertStringContainsString('P2', $content);
    $posP1 = strpos($content, 'P1');
    $posP2 = strpos($content, 'P2');
    $this->assertTrue($posP1 !== false && $posP2 !== false && $posP1 < $posP2, 'P1 debe aparecer antes que P2 en el ranking');
});

test('la vista de prendas tiene enlace al ranking', function() {
    $admin = \App\Models\User::factory()->create(['id_rol' => 1]);
    $response = $this->actingAs($admin)->get(route('prendas.index'));
    $response->assertStatus(200);
    $response->assertSee('Ranking de Ventas');
    $response->assertSee(route('prendas.ranking'));
});
