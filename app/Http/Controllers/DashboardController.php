<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // <-- CAMBIADO
use App\Models\Pedido;   // <-- Incluida por si la necesitas más adelante

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsuarios = User::count(); // <-- CAMBIADO
        $pedidosActivos = Pedido::where('estado', 'En proceso')->count();

        return view('dashboard', [
            'totalUsuarios' => $totalUsuarios,
            'pedidosActivos' => $pedidosActivos,
        ]);
    }
}