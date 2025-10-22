<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Prenda;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteDashboardController extends Controller
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    /**
     * Dashboard para clientes
     */
    public function index()
    {
        // Verificar que el usuario sea cliente
        if (!Auth::check() || Auth::user()->id_rol != 3) {
            abort(403, 'Acceso denegado. Solo para clientes.');
        }

        // Buscar cliente basado en el usuario autenticado
        $cliente = Cliente::where('email', Auth::user()->email)->first();
        
        // Estadísticas del cliente
        $estadisticas = [
            'total_pedidos' => 0,
            'pedidos_activos' => 0,
            'pedidos_completados' => 0,
            'total_gastado' => 0
        ];

        $pedidos_recientes = collect();
        $productos_populares = Prenda::activas()->take(6)->get();

        if ($cliente) {
            $pedidos = Pedido::where('id_cliente', $cliente->id)->get();
            
            $estadisticas = [
                'total_pedidos' => $pedidos->count(),
                'pedidos_activos' => $pedidos->whereIn('estado', ['En proceso', 'Asignado', 'En producción'])->count(),
                'pedidos_completados' => $pedidos->whereIn('estado', ['Terminado', 'Entregado'])->count(),
                'total_gastado' => $pedidos->where('estado', '!=', 'Cancelado')->sum('total')
            ];

            $pedidos_recientes = $pedidos->sortByDesc('created_at')->take(5);
        }

        // Registrar acceso al dashboard
        $this->bitacoraService->registrarActividad(
            'VIEW',
            'DASHBOARD',
            'Cliente ' . Auth::user()->nombre . ' accedió a su dashboard personal'
        );

        return view('cliente.dashboard', compact('estadisticas', 'pedidos_recientes', 'productos_populares', 'cliente'));
    }
}