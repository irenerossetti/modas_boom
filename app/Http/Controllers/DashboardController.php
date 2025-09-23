<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario; // Importa los modelos que necesites
use App\Models\Pedido;

class DashboardController extends Controller
{
    public function index()
    {
        // Obtenemos los datos de la base de datos
        $totalUsuarios = Usuario::count();
        $pedidosActivos = Pedido::where('estado', 'En proceso')->count();
        // ... aquí calcularías el inventario, ingresos, etc.

        // Pasamos los datos a la vista
        return view('dashboard', [
            'totalUsuarios' => $totalUsuarios,
            'pedidosActivos' => $pedidosActivos,
            // ... etc
        ]);
    }
}