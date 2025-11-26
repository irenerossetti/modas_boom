<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Prenda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SistemaController extends Controller
{
    /**
     * Mostrar diagrama y estadísticas del sistema
     */
    public function index()
    {
        // Estadísticas generales
        $stats = [
            'usuarios' => User::count(),
            'usuarios_activos' => User::whereRaw('"habilitado" = true')->count(),
            'roles' => Rol::count(),
            'clientes' => Cliente::count(),
            'pedidos' => Pedido::count(),
            'pedidos_activos' => Pedido::whereNotIn('estado', ['Entregado', 'Cancelado'])->count(),
            'prendas' => Prenda::count(),
            'prendas_activas' => Prenda::whereRaw('"activo" = true')->count(),
            'stock_total' => Prenda::sum('stock'),
        ];

        // Estadísticas por rol
        $usuariosPorRol = User::with('rol')
            ->select('id_rol', DB::raw('count(*) as total'))
            ->groupBy('id_rol')
            ->get()
            ->map(function($item) {
                return [
                    'rol' => $item->rol->nombre ?? 'Sin rol',
                    'total' => $item->total
                ];
            });

        // Pedidos por estado
        $pedidosPorEstado = Pedido::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        // Prendas por categoría
        $prendasPorCategoria = Prenda::select('categoria', DB::raw('count(*) as total'), DB::raw('sum(stock) as stock_total'))
            ->whereRaw('"activo" = true')
            ->groupBy('categoria')
            ->get();

        // Actividad reciente (últimos pedidos)
        $actividadReciente = Pedido::with(['cliente'])
            ->latest()
            ->take(10)
            ->get();

        // Información del sistema
        $sistemaInfo = [
            'version_laravel' => app()->version(),
            'version_php' => PHP_VERSION,
            'base_datos' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'timezone' => config('app.timezone'),
            'debug_mode' => config('app.debug'),
        ];

        // Performance metrics
        $performance = [
            'cache_hits' => Cache::get('cache_hits', 0),
            'db_queries' => 0, // Se puede implementar con query log
            'response_time' => round(microtime(true) - LARAVEL_START, 3) . 's',
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        ];

        return view('sistema.index', compact(
            'stats', 
            'usuariosPorRol', 
            'pedidosPorEstado', 
            'prendasPorCategoria', 
            'actividadReciente',
            'sistemaInfo',
            'performance'
        ));
    }

    /**
     * Mostrar diagrama de arquitectura
     */
    public function diagrama()
    {
        return view('sistema.diagrama');
    }

    /**
     * API para obtener estadísticas en tiempo real
     */
    public function estadisticas()
    {
        $stats = [
            'usuarios_online' => User::whereRaw('"habilitado" = true')->count(), // Se puede mejorar con sesiones
            'pedidos_hoy' => Pedido::whereDate('created_at', today())->count(),
            'stock_bajo' => Prenda::whereRaw('"activo" = true')->where('stock', '<', 10)->count(),
            'timestamp' => now()->format('H:i:s')
        ];

        return response()->json($stats);
    }
}
