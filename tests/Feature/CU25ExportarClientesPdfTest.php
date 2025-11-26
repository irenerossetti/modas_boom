<?php

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin puede exportar lista de clientes a PDF o ver vista HTML', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    Cliente::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('clientes.exportar-pdf'));

    $response->assertStatus(200);
});

test('admin puede exportar lista de clientes en CSV', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    Cliente::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('clientes.exportar-pdf', ['format' => 'csv']));

    $response->assertStatus(200);
    $contentType = $response->headers->get('content-type');
    $this->assertStringContainsString('text/csv', $contentType);
    $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    // Assert the delimiter in header line (skip UTF-8 BOM if present)
    $content = $response->getContent();
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    $firstLine = strtok($content, "\n");
    $this->assertStringContainsString(config('exports.csv_delimiter', ';'), $firstLine);
});

test('admin puede exportar lista de clientes en JSON', function () {
    $admin = User::factory()->create(['id_rol' => 1]);
    Cliente::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('clientes.exportar-pdf', ['format' => 'json']));

    $response->assertStatus(200);
    $this->assertStringContainsString('application/json', $response->headers->get('content-type'));
    $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
});

test('no admin no puede exportar clientes', function () {
    $empleado = User::factory()->create(['id_rol' => 2]);
    Cliente::factory()->count(2)->create();

    $response = $this->actingAs($empleado)->get(route('clientes.exportar-pdf'));

    $response->assertStatus(403);
});