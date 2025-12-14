<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Prenda;
use App\Models\Tela;
use App\Models\Pedido;
use App\Models\CompraInsumo;
use App\Models\Pago;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'format' => 'required|in:pdf,csv,json',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ]);

        $format = $request->format;
        $desde = $request->desde ? date('Y-m-d 00:00:00', strtotime($request->desde)) : null;
        $hasta = $request->hasta ? date('Y-m-d 23:59:59', strtotime($request->hasta)) : null;

        // Gather data
        $productos = Prenda::orderBy('nombre')->get();
        $telas = Tela::orderBy('nombre')->get();

        $pedidosQuery = Pedido::query();
        if ($desde) $pedidosQuery->where('created_at', '>=', $desde);
        if ($hasta) $pedidosQuery->where('created_at', '<=', $hasta);
        $ventas = $pedidosQuery->with('cliente')->orderBy('created_at', 'desc')->get();

        $comprasQuery = CompraInsumo::query();
        if ($desde) $comprasQuery->where('fecha_compra', '>=', $desde);
        if ($hasta) $comprasQuery->where('fecha_compra', '<=', $hasta);
        $compras = $comprasQuery->with('proveedor', 'tela')->orderBy('fecha_compra', 'desc')->get();

        $sections = $request->get('sections', ['productos','telas','ventas','compras']);

        $reportData = [
            'productos' => $productos,
            'telas' => $telas,
            'ventas' => $ventas,
            'compras' => $compras,
            'generated_at' => now()->toDateTimeString(),
            'desde' => $request->desde,
            'hasta' => $request->hasta,
        ];

        if ($format === 'json') {
            $filename = 'report_' . now()->format('Ymd_His') . '.json';
            // Only include selected sections
            $filtered = [];
            foreach ($sections as $s) {
                if (isset($reportData[$s])) $filtered[$s] = $reportData[$s];
            }
            $filtered['generated_at'] = $reportData['generated_at'];
            $filtered['desde'] = $reportData['desde'];
            $filtered['hasta'] = $reportData['hasta'];
            $json = json_encode($filtered, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return response($json, 200, [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        if ($format === 'csv') {
            $filename = 'report_' . now()->format('Ymd_His') . '.csv';
            $callback = function () use ($reportData, $sections) {
                $FH = fopen('php://output', 'w');
                // Productos
                if (in_array('productos', $sections)) {
                fputcsv($FH, ['Productos']);
                fputcsv($FH, ['ID', 'Nombre', 'Descripcion', 'Precio']);
                foreach ($reportData['productos'] as $p) {
                    fputcsv($FH, [$p->id, $p->nombre, $p->descripcion ?? '', $p->precio ?? '']);
                }
                fputcsv($FH, []);
                }

                if (in_array('telas', $sections)) {
                // Telas (Inventario)
                fputcsv($FH, ['Telas (Inventario)']);
                fputcsv($FH, ['ID', 'Nombre', 'Stock', 'Unidad', 'Stock_Minimo']);
                foreach ($reportData['telas'] as $t) {
                    fputcsv($FH, [$t->id, $t->nombre, $t->stock, $t->unidad, $t->stock_minimo]);
                }
                fputcsv($FH, []);
                }

                if (in_array('ventas', $sections)) {
                // Ventas
                fputcsv($FH, ['Ventas']);
                fputcsv($FH, ['ID', 'Cliente', 'Total', 'Fecha']);
                foreach ($reportData['ventas'] as $v) {
                    fputcsv($FH, [$v->id_pedido ?? $v->id, $v->cliente->nombre ?? 'N/A', $v->total ?? 0, $v->created_at]);
                }

                fputcsv($FH, []);
                }

                if (in_array('compras', $sections)) {
                // Compras
                fputcsv($FH, ['Compras']);
                fputcsv($FH, ['ID', 'Proveedor', 'Descripcion', 'Monto', 'Fecha', 'Tela', 'Cantidad']);
                foreach ($reportData['compras'] as $c) {
                    fputcsv($FH, [$c->id, $c->proveedor->nombre ?? 'N/A', $c->descripcion ?? '', $c->monto, $c->fecha_compra, $c->tela->nombre ?? '', $c->cantidad]);
                }
                }
                fclose($FH);
            };
            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // PDF generation
        $filename = 'report_' . now()->format('Ymd_His') . '.pdf';
        $pdf = Pdf::loadView('reports.pdf.report', $reportData);
        return $pdf->download($filename);
    }

    /**
     * Análisis de Productos Estrella (más vendidos) y Hueso (menos vendidos)
     */
    public function analisisProductos(Request $request)
    {
        // Si no hay parámetros, mostrar la vista del formulario
        if (!$request->hasAny(['fecha_desde', 'fecha_hasta', 'formato']) || !$request->has('formato')) {
            return view('reports.analisis-productos');
        }
        

        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'formato' => 'nullable|in:pdf,json,csv,excel',
        ]);

        $fechaDesde = $request->fecha_desde;
        $fechaHasta = $request->fecha_hasta;
        $formato = $request->formato ?? 'pdf';

        // Consulta optimizada para productos más vendidos (Estrella)
        $productosEstrella = Prenda::select('prendas.*')
            ->selectRaw('COALESCE(SUM(pedido_prenda.cantidad), 0) as total_vendido')
            ->selectRaw('COUNT(DISTINCT pedido_prenda.pedido_id) as num_pedidos')
            ->selectRaw('COALESCE(SUM(pedido_prenda.cantidad * pedido_prenda.precio_unitario), 0) as ingresos_totales')
            ->leftJoin('pedido_prenda', 'prendas.id', '=', 'pedido_prenda.prenda_id')
            ->leftJoin('pedido', 'pedido_prenda.pedido_id', '=', 'pedido.id_pedido')
            ->where(function($query) use ($fechaDesde, $fechaHasta) {
                $query->whereNull('pedido.id_pedido') // Incluir productos sin ventas
                      ->orWhere(function($q) use ($fechaDesde, $fechaHasta) {
                          $q->where('pedido.estado', '!=', 'Cancelado');
                          if ($fechaDesde) {
                              $q->whereDate('pedido.created_at', '>=', $fechaDesde);
                          }
                          if ($fechaHasta) {
                              $q->whereDate('pedido.created_at', '<=', $fechaHasta);
                          }
                      });
            })
            ->groupBy('prendas.id', 'prendas.nombre', 'prendas.descripcion', 'prendas.precio', 
                      'prendas.categoria', 'prendas.imagen', 'prendas.stock', 'prendas.activo',
                      'prendas.created_at', 'prendas.updated_at')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        // Consulta para productos menos vendidos (Hueso)
        $productosHueso = Prenda::select('prendas.*')
            ->selectRaw('COALESCE(SUM(pedido_prenda.cantidad), 0) as total_vendido')
            ->selectRaw('COUNT(DISTINCT pedido_prenda.pedido_id) as num_pedidos')
            ->selectRaw('COALESCE(SUM(pedido_prenda.cantidad * pedido_prenda.precio_unitario), 0) as ingresos_totales')
            ->leftJoin('pedido_prenda', 'prendas.id', '=', 'pedido_prenda.prenda_id')
            ->leftJoin('pedido', 'pedido_prenda.pedido_id', '=', 'pedido.id_pedido')
            ->where(function($query) use ($fechaDesde, $fechaHasta) {
                $query->whereNull('pedido.id_pedido') // Incluir productos sin ventas
                      ->orWhere(function($q) use ($fechaDesde, $fechaHasta) {
                          $q->where('pedido.estado', '!=', 'Cancelado');
                          if ($fechaDesde) {
                              $q->whereDate('pedido.created_at', '>=', $fechaDesde);
                          }
                          if ($fechaHasta) {
                              $q->whereDate('pedido.created_at', '<=', $fechaHasta);
                          }
                      });
            })
            ->groupBy('prendas.id', 'prendas.nombre', 'prendas.descripcion', 'prendas.precio', 
                      'prendas.categoria', 'prendas.imagen', 'prendas.stock', 'prendas.activo',
                      'prendas.created_at', 'prendas.updated_at')
            ->orderBy('total_vendido', 'asc')
            ->limit(10)
            ->get();

        // Estadísticas generales
        $totalProductos = Prenda::count();
        $productosConVentas = Prenda::whereHas('pedidos', function($query) use ($fechaDesde, $fechaHasta) {
            $query->where('estado', '!=', 'Cancelado');
            if ($fechaDesde) {
                $query->whereDate('pedido.created_at', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $query->whereDate('pedido.created_at', '<=', $fechaHasta);
            }
        })->count();
        $productosSinVentas = $totalProductos - $productosConVentas;

        $data = [
            'productos_estrella' => $productosEstrella,
            'productos_hueso' => $productosHueso,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'total_productos' => $totalProductos,
            'productos_con_ventas' => $productosConVentas,
            'productos_sin_ventas' => $productosSinVentas,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];

        if ($formato === 'json') {
            return response()->json($data);
        }

        if ($formato === 'csv') {
            $filename = 'analisis_productos_' . now()->format('Ymd_His') . '.csv';
            $callback = function() use ($data) {
                $FH = fopen('php://output', 'w');
                // BOM UTF-8 para Excel
                echo chr(0xEF) . chr(0xBB) . chr(0xBF);
                
                // Encabezado del reporte
                fputcsv($FH, ['ANÁLISIS DE PRODUCTOS - ESTRELLA Y HUESO'], ';');
                fputcsv($FH, ['Fecha de Generación: ' . $data['generated_at']], ';');
                if ($data['fecha_desde'] || $data['fecha_hasta']) {
                    $periodo = 'Período: ';
                    if ($data['fecha_desde'] && $data['fecha_hasta']) {
                        $periodo .= \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y');
                    } elseif ($data['fecha_desde']) {
                        $periodo .= 'Desde ' . \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y');
                    } else {
                        $periodo .= 'Hasta ' . \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y');
                    }
                    fputcsv($FH, [$periodo], ';');
                }
                fputcsv($FH, [], ';');
                
                // Estadísticas generales
                fputcsv($FH, ['ESTADÍSTICAS GENERALES'], ';');
                fputcsv($FH, ['Total Productos', $data['total_productos']], ';');
                fputcsv($FH, ['Productos con Ventas', $data['productos_con_ventas']], ';');
                fputcsv($FH, ['Productos sin Ventas', $data['productos_sin_ventas']], ';');
                fputcsv($FH, [], ';');
                
                // Productos Estrella
                fputcsv($FH, ['TOP 10 PRODUCTOS ESTRELLA (Más Vendidos)'], ';');
                fputcsv($FH, ['#', 'Producto', 'Categoría', 'Unidades Vendidas', 'Número de Pedidos', 'Ingresos Totales (Bs.)'], ';');
                foreach ($data['productos_estrella'] as $index => $producto) {
                    fputcsv($FH, [
                        $index + 1,
                        $producto->nombre,
                        $producto->categoria ?? 'N/A',
                        number_format($producto->total_vendido, 0, ',', '.'),
                        $producto->num_pedidos,
                        number_format($producto->ingresos_totales, 2, ',', '.')
                    ], ';');
                }
                fputcsv($FH, [], ';');
                
                // Productos Hueso
                fputcsv($FH, ['TOP 10 PRODUCTOS HUESO (Menos Vendidos)'], ';');
                fputcsv($FH, ['#', 'Producto', 'Categoría', 'Unidades Vendidas', 'Número de Pedidos', 'Ingresos Totales (Bs.)', 'Recomendación'], ';');
                foreach ($data['productos_hueso'] as $index => $producto) {
                    $recomendacion = $producto->total_vendido == 0 ? 'Revisar' : 'Promocionar';
                    fputcsv($FH, [
                        $index + 1,
                        $producto->nombre,
                        $producto->categoria ?? 'N/A',
                        number_format($producto->total_vendido, 0, ',', '.'),
                        $producto->num_pedidos,
                        number_format($producto->ingresos_totales, 2, ',', '.'),
                        $recomendacion
                    ], ';');
                }
                
                fclose($FH);
            };
            
            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        if ($formato === 'excel') {
            $filename = 'analisis_productos_' . now()->format('Ymd_His') . '.xls';
            $callback = function() use ($data) {
                // Generar HTML table para Excel
                echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
                echo '<head>';
                echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
                echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
                echo '<x:Name>Análisis Productos</x:Name>';
                echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>';
                echo '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
                echo '<style>';
                echo 'table { border-collapse: collapse; width: 100%; }';
                echo 'th { background-color: #e91e63; color: white; font-weight: bold; padding: 8px; border: 1px solid #ddd; }';
                echo 'td { padding: 8px; border: 1px solid #ddd; }';
                echo '.header { background-color: #f5f5f5; font-weight: bold; }';
                echo '.section-title { background-color: #e91e63; color: white; font-weight: bold; padding: 10px; }';
                echo '.section-title-hueso { background-color: #9e9e9e; color: white; font-weight: bold; padding: 10px; }';
                echo '.numero { text-align: right; }';
                echo '</style>';
                echo '</head>';
                echo '<body>';
                
                echo '<h1>📊 ANÁLISIS DE PRODUCTOS - ESTRELLA Y HUESO</h1>';
                echo '<p><strong>Fecha de Generación:</strong> ' . $data['generated_at'] . '</p>';
                if ($data['fecha_desde'] || $data['fecha_hasta']) {
                    echo '<p><strong>Período de Análisis:</strong> ';
                    if ($data['fecha_desde'] && $data['fecha_hasta']) {
                        echo \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y');
                    } elseif ($data['fecha_desde']) {
                        echo 'Desde ' . \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y');
                    } else {
                        echo 'Hasta ' . \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y');
                    }
                    echo '</p>';
                }
                
                // Estadísticas
                echo '<br><table>';
                echo '<tr class="header"><td colspan="2">ESTADÍSTICAS GENERALES</td></tr>';
                echo '<tr><td>Total Productos</td><td class="numero">' . $data['total_productos'] . '</td></tr>';
                echo '<tr><td>Productos con Ventas</td><td class="numero">' . $data['productos_con_ventas'] . '</td></tr>';
                echo '<tr><td>Productos sin Ventas</td><td class="numero">' . $data['productos_sin_ventas'] . '</td></tr>';
                echo '</table><br><br>';
                
                // Productos Estrella
                echo '<table>';
                echo '<tr class="section-title"><td colspan="6">⭐ TOP 10 PRODUCTOS ESTRELLA (Más Vendidos)</td></tr>';
                echo '<tr>';
                echo '<th>#</th><th>Producto</th><th>Categoría</th><th>Unidades</th><th>Pedidos</th><th>Ingresos (Bs.)</th>';
                echo '</tr>';
                foreach ($data['productos_estrella'] as $index => $producto) {
                    echo '<tr>';
                    echo '<td>' . ($index + 1) . '</td>';
                    echo '<td>' . htmlspecialchars($producto->nombre) . '</td>';
                    echo '<td>' . htmlspecialchars($producto->categoria ?? 'N/A') . '</td>';
                    echo '<td class="numero">' . number_format($producto->total_vendido, 0, ',', '.') . '</td>';
                    echo '<td class="numero">' . $producto->num_pedidos . '</td>';
                    echo '<td class="numero">' . number_format($producto->ingresos_totales, 2, ',', '.') . '</td>';
                    echo '</tr>';
                }
                echo '</table><br><br>';
                
                // Productos Hueso
                echo '<table>';
                echo '<tr class="section-title-hueso"><td colspan="7">💀 TOP 10 PRODUCTOS HUESO (Menos Vendidos)</td></tr>';
                echo '<tr>';
                echo '<th>#</th><th>Producto</th><th>Categoría</th><th>Unidades</th><th>Pedidos</th><th>Ingresos (Bs.)</th><th>Recomendación</th>';
                echo '</tr>';
                foreach ($data['productos_hueso'] as $index => $producto) {
                    $recomendacion = $producto->total_vendido == 0 ? 'Revisar' : 'Promocionar';
                    echo '<tr>';
                    echo '<td>' . ($index + 1) . '</td>';
                    echo '<td>' . htmlspecialchars($producto->nombre) . '</td>';
                    echo '<td>' . htmlspecialchars($producto->categoria ?? 'N/A') . '</td>';
                    echo '<td class="numero">' . number_format($producto->total_vendido, 0, ',', '.') . '</td>';
                    echo '<td class="numero">' . $producto->num_pedidos . '</td>';
                    echo '<td class="numero">' . number_format($producto->ingresos_totales, 2, ',', '.') . '</td>';
                    echo '<td>' . $recomendacion . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
                echo '</body></html>';
            };
            
            return response()->stream($callback, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Generar PDF
        $pdf = Pdf::loadView('reports.pdf.analisis-productos', $data);
        $pdf->setPaper('letter', 'portrait');
        
        $filename = 'analisis_productos_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
    // CU: Reporte de Rentabilidad
    public function rentabilidad(Request $request)
    {
        $prendas = Prenda::orderBy('categoria')->orderBy('nombre')->get();
        
        $data = $prendas->map(function($prenda) {
            $margen = $prenda->precio - $prenda->costo;
            $margenPorcentaje = $prenda->precio > 0 ? ($margen / $prenda->precio) * 100 : 0;
            return [
                'id' => $prenda->id,
                'nombre' => $prenda->nombre,
                'categoria' => $prenda->categoria,
                'precio' => $prenda->precio,
                'costo' => $prenda->costo,
                'margen' => $margen,
                'margen_porcentaje' => $margenPorcentaje
            ];
        });

        if ($request->has('download')) {
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadView('reportes.rentabilidad', compact('data'));
            return $pdf->download('rentabilidad.pdf');
        }

        return view('reportes.rentabilidad-web', compact('data'));
    }
}
