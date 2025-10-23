<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRODUCTOS EN BASE DE DATOS ===\n";
$productos = App\Models\Prenda::all();
echo "Total de productos: " . $productos->count() . "\n\n";

foreach ($productos as $producto) {
    echo "- {$producto->nombre} ({$producto->categoria}) - Bs. {$producto->precio} - Stock: {$producto->stock}\n";
}

echo "\n=== VERIFICANDO CAMPO ACTIVO ===\n";
$activas = App\Models\Prenda::where('activo', true)->count();
$inactivas = App\Models\Prenda::where('activo', false)->count();
$nulas = App\Models\Prenda::whereNull('activo')->count();

echo "Prendas activas (true): {$activas}\n";
echo "Prendas inactivas (false): {$inactivas}\n";
echo "Prendas con activo NULL: {$nulas}\n";

echo "\n=== VERIFICANDO SCOPE ACTIVAS ===\n";
$prendasActivas = App\Models\Prenda::activas()->get();
echo "Prendas encontradas con scope activas(): " . $prendasActivas->count() . "\n";

echo "\n=== VERIFICANDO CONTROLADOR ===\n";
// Limpiar cache primero
Cache::forget('productos_catalogo_db');

$productosControlador = Cache::remember('productos_catalogo_db', 3600, function () {
    return \App\Models\Prenda::activas()
        ->orderBy('categoria')
        ->orderBy('nombre')
        ->get()
        ->map(function ($prenda) {
            return [
                'id' => $prenda->id,
                'nombre' => $prenda->nombre,
                'precio' => $prenda->precio,
                'categoria' => $prenda->categoria,
                'imagen' => $prenda->imagen,
                'descripcion' => $prenda->descripcion,
                'colores' => $prenda->colores ?? [],
                'tallas' => $prenda->tallas ?? [],
                'stock' => $prenda->stock
            ];
        })
        ->toArray();
});

echo "Productos desde controlador: " . count($productosControlador) . "\n";
foreach ($productosControlador as $producto) {
    echo "- {$producto['nombre']} ({$producto['categoria']})\n";
}

echo "\n=== LIMPIANDO CACHE Y PROBANDO DE NUEVO ===\n";
Cache::forget('productos_catalogo_db');
echo "Cache limpiado\n";

// Probar de nuevo sin cache
$productosSinCache = \App\Models\Prenda::activas()
    ->orderBy('categoria')
    ->orderBy('nombre')
    ->get()
    ->map(function ($prenda) {
        return [
            'id' => $prenda->id,
            'nombre' => $prenda->nombre,
            'precio' => $prenda->precio,
            'categoria' => $prenda->categoria,
            'imagen' => $prenda->imagen,
            'descripcion' => $prenda->descripcion,
            'colores' => $prenda->colores ?? [],
            'tallas' => $prenda->tallas ?? [],
            'stock' => $prenda->stock
        ];
    })
    ->toArray();

echo "Productos sin cache: " . count($productosSinCache) . "\n";