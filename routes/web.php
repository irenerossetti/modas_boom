<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\PrendaController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas públicas del catálogo
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::post('/catalogo/pedido', [CatalogoController::class, 'crearPedido'])->name('catalogo.crear-pedido');
Route::get('/catalogo/pedido-confirmado/{id}', [CatalogoController::class, 'pedidoConfirmado'])->name('catalogo.pedido-confirmado');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'user.enabled', 'admin.role'])->name('dashboard');

Route::middleware(['auth', 'user.enabled'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('clientes', [ClienteController::class, 'index'])->name('clientes.index');
    
    // Rutas de pedidos para empleados y administradores
    Route::resource('pedidos', PedidoController::class);
    Route::get('pedidos/{id}/historial', [PedidoController::class, 'historial'])->name('pedidos.historial');
    Route::get('clientes/{id}/pedidos', [PedidoController::class, 'clienteHistorial'])->name('pedidos.cliente-historial');
    
    // Rutas específicas para empleados y administradores - crear pedidos para clientes
    Route::get('crear-pedido-cliente', [PedidoController::class, 'empleadoCrear'])->name('pedidos.empleado-crear');
    Route::post('crear-pedido-cliente', [PedidoController::class, 'empleadoStore'])->name('pedidos.empleado-store');
    
    // Rutas específicas para clientes - crear y ver sus propios pedidos
    Route::get('hacer-pedido', [PedidoController::class, 'clienteCrear'])->name('pedidos.cliente-crear');
    Route::post('hacer-pedido', [PedidoController::class, 'clienteStore'])->name('pedidos.cliente-store');
    Route::get('mis-pedidos', [PedidoController::class, 'misPedidos'])->name('pedidos.mis-pedidos');
    
    // Ruta del catálogo de productos
    Route::get('catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
});

Route::middleware(['auth', 'user.enabled', 'admin.role'])->group(function () {
    Route::resource('clientes', ClienteController::class)->except(['index']);
    Route::resource('users', UserController::class);
    Route::resource('roles', RolController::class);
    Route::resource('prendas', PrendaController::class);
    
    // Rutas de bitácora - solo para administradores
    Route::get('bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
    Route::get('bitacora/limpiar-filtros', [BitacoraController::class, 'limpiarFiltros'])->name('bitacora.limpiar-filtros');
    Route::post('bitacora/exportar', [BitacoraController::class, 'exportar'])->name('bitacora.exportar');
    
    // Rutas adicionales de pedidos - solo para administradores
    Route::post('pedidos/{id}/asignar', [PedidoController::class, 'asignar'])->name('pedidos.asignar');
    Route::get('pedidos-por-operario', [PedidoController::class, 'porOperario'])->name('pedidos.por-operario');
});

Route::get('/empleado-dashboard', function () {
    return view('empleado-dashboard');
})->middleware(['auth', 'verified', 'user.enabled'])->name('empleado.dashboard');



require __DIR__.'/auth.php';
