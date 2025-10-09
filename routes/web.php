<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\BitacoraController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'user.enabled', 'admin.role'])->name('dashboard');

Route::middleware(['auth', 'user.enabled'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('clientes', [ClienteController::class, 'index'])->name('clientes.index');
});

Route::middleware(['auth', 'user.enabled', 'admin.role'])->group(function () {
    Route::resource('clientes', ClienteController::class)->except(['index']);
    Route::resource('users', UserController::class);
    Route::resource('roles', RolController::class);
    
    // Rutas de bitácora - solo para administradores
    Route::get('bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
    Route::get('bitacora/limpiar-filtros', [BitacoraController::class, 'limpiarFiltros'])->name('bitacora.limpiar-filtros');
    Route::post('bitacora/exportar', [BitacoraController::class, 'exportar'])->name('bitacora.exportar');
});

Route::get('/empleado-dashboard', function () {
    return view('empleado-dashboard');
})->middleware(['auth', 'verified', 'user.enabled'])->name('empleado.dashboard');

require __DIR__.'/auth.php';
