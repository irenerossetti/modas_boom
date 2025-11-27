<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PagoController extends Controller
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    // CU29: Registrar pago del pedido (admin)
    public function create($pedidoId)
    {
        $pedido = Pedido::with('cliente')->findOrFail($pedidoId);
        return view('pagos.create', compact('pedido'));
    }

    public function store(Request $request, $pedidoId)
    {
        $pedido = Pedido::with('cliente')->findOrFail($pedidoId);

        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo' => 'nullable|string|max:100',
            'referencia' => 'nullable|string|max:255',
            'fecha_pago' => 'nullable|date',
        ]);

        $cliente = $pedido->cliente;

        $pago = Pago::create([
            'id_pedido' => $pedido->id_pedido,
            'id_cliente' => $cliente->id ?? null,
            'monto' => $request->monto,
            'metodo' => $request->metodo,
            'referencia' => $request->referencia,
            'fecha_pago' => $request->fecha_pago ?? now(),
            'registrado_por' => auth()->id(),
        ]);

        // Registrar en bitácora
        $mensaje = auth()->user()->nombre . " registró un pago de Bs. {$pago->monto} para el pedido #{$pedido->id_pedido}";
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PAGOS',
            $mensaje,
            null,
            $pago->toArray()
        );

        // También registrar una entrada en el historial del pedido (PEDIDOS) para ser visible en su historial
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PEDIDOS',
            $mensaje,
            null,
            array_merge($pago->toArray(), ['pedido_id' => $pedido->id_pedido])
        );

        return redirect()->route('pedidos.show', $pedido->id_pedido)->with('success', 'Pago registrado correctamente.');
    }

    // CU30: Emitir recibo digital
    public function emitirRecibo($pagoId)
    {
        $pago = Pago::with(['pedido.cliente', 'registradoPor'])->findOrFail($pagoId);
        // Use dompdf wrapper and ensure options are set for proper unicode rendering
        $pdfWrapper = app()->make('dompdf.wrapper');
        $pdfWrapper->loadView('pagos.recibo', compact('pago'));
        // Ensure proper parser and font for UTF-8 content
        $pdfWrapper->setOption('isHtml5ParserEnabled', true);
        $pdfWrapper->setOption('isRemoteEnabled', false);
        // Force font subsetting to ensure DejaVu Sans is embedded in the PDF
        $pdfWrapper->setOption('enable_font_subsetting', true);
        $pdfWrapper->setOption('defaultFont', 'DejaVu Sans');
        // Disable inline PHP in documents for security
        $pdfWrapper->setOption('isPhpEnabled', false);
        $pdfWrapper->setPaper('A4');
        $pdfContent = $pdfWrapper->output();
        $filename = 'recibo_pago_' . $pago->id . '.pdf';
        // Save to storage and link (binary content)
        Storage::put('public/recibos/'.$filename, $pdfContent);
        $pago->recibo_path = 'recibos/'.$filename;
        $pago->save();

        // For normal use we download, but if the request asks for `stream` query param we stream inline
        if (request()->query('view') == 'inline') {
            return $pdfWrapper->stream($filename);
        }
        return $pdfWrapper->download($filename);
    }

    // Helper route for debugging: stream instead of download
    public function emitirReciboStream($pagoId)
    {
        return $this->emitirRecibo($pagoId);
    }

    // CU31: Consultar estado de pago del cliente (admin)
    public function clientePagos($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $pagos = Pago::where('id_cliente', $cliente->id)->orderBy('fecha_pago', 'desc')->get();
        $totalPagado = $pagos->where('anulado', false)->sum('monto');
        return view('pagos.cliente-pagos', compact('cliente', 'pagos', 'totalPagado'));
    }

    // CU32: Anular pago registrado por error
    public function anular(Request $request, $pagoId)
    {
        $pago = Pago::findOrFail($pagoId);
        $request->validate([
            'motivo' => 'required|string|max:1000'
        ]);
        // Usamos SQL explícito para forzar booleano true en PostgreSQL y evitar cast a integer
        DB::update(
            'UPDATE "pago" SET "anulado" = true, "anulado_por" = ?, "anulado_motivo" = ?, "updated_at" = ? WHERE "id" = ?',
            [auth()->id(), $request->motivo, now(), $pago->id]
        );

        $pago->refresh();

        $mensaje = auth()->user()->nombre . " anuló el pago #{$pago->id} (Motivo: {$request->motivo})";
        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PAGOS',
            $mensaje,
            null,
            $pago->toArray()
        );
        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PEDIDOS',
            $mensaje,
            null,
            array_merge($pago->toArray(), ['pedido_id' => $pago->id_pedido])
        );

        return redirect()->back()->with('success', 'Pago anulado correctamente.');
    }

    // Admin listing for management
    public function index()
    {
        $pagos = Pago::with(['pedido', 'cliente', 'registradoPor'])->orderBy('created_at', 'desc')->paginate(25);
        return view('pagos.index', compact('pagos'));
    }
}
