<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\AvanceProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportePedidosEntregadosController extends Controller
{
    /**
     * Mostrar reporte de pedidos entregados
     */
    public function index(Request $request)
    {
        // Verificar permisos (solo admin y empleados)
        if (!in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para acceder a este reporte.');
        }

        // Filtros
        $fechaDesde = $request->get('fecha_desde', now()->subMonth()->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        $conCalificacion = $request->get('con_calificacion');
        $calificacionMin = $request->get('calificacion_min');

        // Consulta base
        $query = Pedido::with(['cliente', 'avancesProduccion'])
            ->where('estado', 'Entregado')
            ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);

        // Filtro por calificación
        if ($conCalificacion === 'si') {
            $query->whereNotNull('calificacion');
        } elseif ($conCalificacion === 'no') {
            $query->whereNull('calificacion');
        }

        if ($calificacionMin) {
            $query->where('calificacion', '>=', $calificacionMin);
        }

        $pedidos = $query->orderBy('updated_at', 'desc')->paginate(15);

        // Estadísticas
        $estadisticas = [
            'total_entregados' => Pedido::where('estado', 'Entregado')
                ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->count(),
            'con_calificacion' => Pedido::where('estado', 'Entregado')
                ->whereNotNull('calificacion')
                ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->count(),
            'promedio_calificacion' => Pedido::where('estado', 'Entregado')
                ->whereNotNull('calificacion')
                ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->avg('calificacion'),
            'por_calificacion' => Pedido::where('estado', 'Entregado')
                ->whereNotNull('calificacion')
                ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->selectRaw('calificacion, COUNT(*) as total')
                ->groupBy('calificacion')
                ->orderBy('calificacion')
                ->get()
        ];

        return view('reportes.pedidos-entregados', compact(
            'pedidos', 
            'estadisticas', 
            'fechaDesde', 
            'fechaHasta', 
            'conCalificacion', 
            'calificacionMin'
        ));
    }

    /**
     * Exportar reporte a PDF
     */
    public function exportarPDF(Request $request)
    {
        // Verificar permisos
        if (!in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para exportar este reporte.');
        }

        $fechaDesde = $request->get('fecha_desde', now()->subMonth()->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        $conCalificacion = $request->get('con_calificacion');
        $calificacionMin = $request->get('calificacion_min');

        // Obtener datos
        $query = Pedido::with(['cliente', 'avancesProduccion'])
            ->where('estado', 'Entregado')
            ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);

        if ($conCalificacion === 'si') {
            $query->whereNotNull('calificacion');
        } elseif ($conCalificacion === 'no') {
            $query->whereNull('calificacion');
        }

        if ($calificacionMin) {
            $query->where('calificacion', '>=', $calificacionMin);
        }

        $pedidos = $query->orderBy('updated_at', 'desc')->get();

        // Estadísticas
        $estadisticas = [
            'total_entregados' => Pedido::where('estado', 'Entregado')
                ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->count(),
            'con_calificacion' => Pedido::where('estado', 'Entregado')
                ->whereNotNull('calificacion')
                ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->count(),
            'promedio_calificacion' => round(Pedido::where('estado', 'Entregado')
                ->whereNotNull('calificacion')
                ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                ->avg('calificacion'), 2)
        ];

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reportes.pdf.pedidos-entregados', compact(
            'pedidos', 
            'estadisticas', 
            'fechaDesde', 
            'fechaHasta'
        ));

        return $pdf->download('reporte-pedidos-entregados-' . $fechaDesde . '-' . $fechaHasta . '.pdf');
    }

    /**
     * Exportar reporte a Excel/CSV
     */
    public function exportarCSV(Request $request)
    {
        // Verificar permisos
        if (!in_array(Auth::user()->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para exportar este reporte.');
        }

        $fechaDesde = $request->get('fecha_desde', now()->subMonth()->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        $conCalificacion = $request->get('con_calificacion');
        $calificacionMin = $request->get('calificacion_min');

        // Obtener datos
        $query = Pedido::with(['cliente'])
            ->where('estado', 'Entregado')
            ->whereBetween('updated_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);

        if ($conCalificacion === 'si') {
            $query->whereNotNull('calificacion');
        } elseif ($conCalificacion === 'no') {
            $query->whereNull('calificacion');
        }

        if ($calificacionMin) {
            $query->where('calificacion', '>=', $calificacionMin);
        }

        $pedidos = $query->orderBy('updated_at', 'desc')->get();

        $filename = 'reporte-pedidos-entregados-' . $fechaDesde . '-' . $fechaHasta . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($pedidos) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, [
                'Número Pedido',
                'Cliente',
                'CI/NIT',
                'Total',
                'Fecha Creación',
                'Fecha Entrega',
                'Calificación',
                'Calificación Texto',
                'Comentario',
                'Fecha Calificación'
            ]);

            // Datos
            foreach ($pedidos as $pedido) {
                fputcsv($file, [
                    $pedido->id_pedido,
                    $pedido->cliente->nombre . ' ' . $pedido->cliente->apellido,
                    $pedido->cliente->ci_nit,
                    $pedido->total ? 'Bs. ' . number_format($pedido->total, 2) : 'No definido',
                    $pedido->created_at->format('d/m/Y H:i'),
                    $pedido->updated_at->format('d/m/Y H:i'),
                    $pedido->calificacion ?? 'Sin calificar',
                    $pedido->calificacion_texto,
                    $pedido->comentario_calificacion ?? '',
                    $pedido->fecha_calificacion ? $pedido->fecha_calificacion->format('d/m/Y H:i') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}