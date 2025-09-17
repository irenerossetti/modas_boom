<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
// Entra a / y redirige al dashboard (si no está logueado,
// el middleware de /dashboard lo enviará al login de Breeze).
Route::redirect('/', '/dashboard');

// Ruta protegida del dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// RUTAS DE PERFIL (necesarias para route('profile.edit'))
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// Rutas de autenticación generadas por Breeze (login, register, logout, etc.)
require __DIR__ . '/auth.php';
