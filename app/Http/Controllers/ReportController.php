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
}
