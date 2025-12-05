<?php

namespace App\Http\Controllers;

use App\Models\AvanceProduccion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteProduccionController extends Controller
{
    /**
     * Mostrar formulario de reporte de rendimiento por operario
     */
    public function index()
    {
        // Verificar que el usuario sea administrador
        if (!auth()->check() || auth()->user()->id_rol !== 1) {
            abort(403, 'No tienes permisos para acceder a este reporte.');
        }

        // Obtener empleados (operarios)
        $operarios = User::where('id_rol', 2)
            ->orderBy('nombre')
            ->get()
            ->filter(function($u) { return $u->habilitado; });

        return view('reportes.produccion.index', compact('operarios'));
    }

    /**
     * Generar reporte de rendimiento por operario
     */
    public function rendimientoPorOperario(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->check() || auth()->user()->id_rol !== 1) {
            abort(403, 'No tienes permisos para acceder a este reporte.');
        }

        $request->validate([
            'operario_id' => 'nullable|exists:usuario,id_usuario',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        // Construir consulta base
        $query = AvanceProduccion::with(['operario', 'pedido.cliente', 'registradoPor'])
            ->whereNotNull('user_id_operario');

        // Filtrar por operario si se especifica
        if ($request->operario_id) {
            $query->where('user_id_operario', $request->operario_id);
        }

        // Filtrar por rango de fechas
        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Obtener avances
        $avances = $query->orderBy('created_at', 'desc')->get();

        // Calcular estadísticas por operario
        $estadisticas = $avances->groupBy('user_id_operario')->map(function ($avancesOperario) {
            $operario = $avancesOperario->first()->operario;
            
            return [
                'operario' => $operario,
                'total_avances' => $avancesOperario->count(),
                'total_prendas_procesadas' => $avancesOperario->sum(function ($avance) {
                    // Contar prendas del pedido asociado
                    return $avance->pedido->prendas->sum('pivot.cantidad') ?? 0;
                }),
                'total_a_pagar' => $avancesOperario->sum('costo_mano_obra'),
                'promedio_por_avance' => $avancesOperario->avg('costo_mano_obra'),
                'etapas' => $avancesOperario->pluck('etapa')->unique()->values(),
                'avances' => $avancesOperario,
            ];
        });

        // Calcular totales generales
        $totales = [
            'total_avances' => $avances->count(),
            'total_prendas' => $estadisticas->sum('total_prendas_procesadas'),
            'total_a_pagar' => $avances->sum('costo_mano_obra'),
            'promedio_general' => $avances->avg('costo_mano_obra'),
        ];

        // Obtener operarios para el filtro
        $operarios = User::where('id_rol', 2)
            ->orderBy('nombre')
            ->get()
            ->filter(function($u) { return $u->habilitado; });

        // Operario seleccionado
        $operarioSeleccionado = $request->operario_id 
            ? User::find($request->operario_id) 
            : null;

        return view('reportes.produccion.rendimiento', compact(
            'estadisticas',
            'totales',
            'operarios',
            'operarioSeleccionado',
            'avances'
        ))->with([
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
        ]);
    }

    /**
     * Exportar reporte a CSV
     */
    public function exportarCSV(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->check() || auth()->user()->id_rol !== 1) {
            abort(403, 'No tienes permisos para exportar este reporte.');
        }

        $request->validate([
            'operario_id' => 'nullable|exists:usuario,id_usuario',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        // Construir consulta
        $query = AvanceProduccion::with(['operario', 'pedido.cliente', 'registradoPor'])
            ->whereNotNull('user_id_operario');

        if ($request->operario_id) {
            $query->where('user_id_operario', $request->operario_id);
        }

        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $avances = $query->orderBy('created_at', 'desc')->get();

        $filename = 'reporte_produccion_' . date('Y-m-d_His') . '.csv';

        $callback = function() use ($avances) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'Fecha',
                'Operario',
                'Pedido',
                'Cliente',
                'Etapa',
                'Porcentaje',
                'Costo (Bs.)',
                'Descripcion'
            ], ';');

            // Datos
            foreach ($avances as $avance) {
                fputcsv($file, [
                    $avance->created_at->format('d/m/Y H:i'),
                    $avance->operario->nombre ?? 'N/A',
                    '#' . $avance->id_pedido,
                    $avance->pedido->cliente->nombre ?? 'N/A',
                    $avance->etapa,
                    $avance->porcentaje_avance . '%',
                    number_format($avance->costo_mano_obra, 2),
                    $avance->descripcion
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Exportar reporte a Excel
     */
    public function exportarExcel(Request $request)
    {
        // Por ahora, Excel usará el mismo formato CSV pero con extensión .xlsx
        // Para una implementación completa, se recomienda usar PhpSpreadsheet
        
        // Verificar que el usuario sea administrador
        if (!auth()->check() || auth()->user()->id_rol !== 1) {
            abort(403, 'No tienes permisos para exportar este reporte.');
        }

        $request->validate([
            'operario_id' => 'nullable|exists:usuario,id_usuario',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        // Construir consulta
        $query = AvanceProduccion::with(['operario', 'pedido.cliente', 'registradoPor'])
            ->whereNotNull('user_id_operario');

        if ($request->operario_id) {
            $query->where('user_id_operario', $request->operario_id);
        }

        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $avances = $query->orderBy('created_at', 'desc')->get();

        // Calcular estadísticas
        $estadisticas = $avances->groupBy('user_id_operario')->map(function ($avancesOperario) {
            $operario = $avancesOperario->first()->operario;
            
            return [
                'operario' => $operario->nombre,
                'total_avances' => $avancesOperario->count(),
                'total_prendas' => $avancesOperario->sum(function ($avance) {
                    return $avance->pedido->prendas->sum('pivot.cantidad') ?? 0;
                }),
                'total_a_pagar' => $avancesOperario->sum('costo_mano_obra'),
            ];
        });

        $filename = 'reporte_produccion_' . date('Y-m-d_His') . '.csv';

        $callback = function() use ($avances, $estadisticas) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Resumen por operario
            fputcsv($file, ['RESUMEN POR OPERARIO'], ';');
            fputcsv($file, ['Operario', 'Total Avances', 'Total Prendas', 'Total a Pagar (Bs.)'], ';');
            
            foreach ($estadisticas as $stat) {
                fputcsv($file, [
                    $stat['operario'],
                    $stat['total_avances'],
                    $stat['total_prendas'],
                    number_format($stat['total_a_pagar'], 2)
                ], ';');
            }
            
            // Línea en blanco
            fputcsv($file, [], ';');
            
            // Detalle de avances
            fputcsv($file, ['DETALLE DE AVANCES'], ';');
            fputcsv($file, [
                'Fecha',
                'Operario',
                'Pedido',
                'Cliente',
                'Etapa',
                'Porcentaje',
                'Costo (Bs.)',
                'Descripcion'
            ], ';');

            foreach ($avances as $avance) {
                fputcsv($file, [
                    $avance->created_at->format('d/m/Y H:i'),
                    $avance->operario->nombre ?? 'N/A',
                    '#' . $avance->id_pedido,
                    $avance->pedido->cliente->nombre ?? 'N/A',
                    $avance->etapa,
                    $avance->porcentaje_avance . '%',
                    number_format($avance->costo_mano_obra, 2),
                    $avance->descripcion
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Exportar reporte a PDF
     */
    public function exportarPDF(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->check() || auth()->user()->id_rol !== 1) {
            abort(403, 'No tienes permisos para exportar este reporte.');
        }

        // Reutilizar la lógica de rendimientoPorOperario
        $request->validate([
            'operario_id' => 'nullable|exists:usuario,id_usuario',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        // Construir consulta base
        $query = AvanceProduccion::with(['operario', 'pedido.cliente', 'registradoPor'])
            ->whereNotNull('user_id_operario');

        if ($request->operario_id) {
            $query->where('user_id_operario', $request->operario_id);
        }

        if ($request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $avances = $query->orderBy('created_at', 'desc')->get();

        // Calcular estadísticas
        $estadisticas = $avances->groupBy('user_id_operario')->map(function ($avancesOperario) {
            $operario = $avancesOperario->first()->operario;
            
            return [
                'operario' => $operario,
                'total_avances' => $avancesOperario->count(),
                'total_prendas_procesadas' => $avancesOperario->sum(function ($avance) {
                    return $avance->pedido->prendas->sum('pivot.cantidad') ?? 0;
                }),
                'total_a_pagar' => $avancesOperario->sum('costo_mano_obra'),
                'promedio_por_avance' => $avancesOperario->avg('costo_mano_obra'),
            ];
        });

        $totales = [
            'total_avances' => $avances->count(),
            'total_prendas' => $estadisticas->sum('total_prendas_procesadas'),
            'total_a_pagar' => $avances->sum('costo_mano_obra'),
        ];

        // Renderizar la vista con UTF-8
        $html = view('reportes.produccion.pdf', compact('estadisticas', 'totales', 'avances'))->render();
        
        // Configurar DomPDF con opciones específicas para UTF-8
        $pdf = \PDF::loadHTML($html)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'isFontSubsettingEnabled' => true,
                'defaultMediaType' => 'print',
                'isCssFloatEnabled' => true,
                'isPhpEnabled' => false,
            ]);

        $filename = 'reporte_produccion_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }
}
