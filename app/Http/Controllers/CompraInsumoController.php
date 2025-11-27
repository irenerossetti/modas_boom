<?php

namespace App\Http\Controllers;

use App\Models\CompraInsumo;
use App\Models\Proveedor;
use App\Services\BitacoraService;
use Illuminate\Http\Request;

class CompraInsumoController extends Controller
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    public function index()
    {
        $compras = CompraInsumo::with('proveedor')->orderBy('fecha_compra', 'desc')->paginate(20);
        return view('inventario.compras.index', compact('compras'));
    }

    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();
        return view('inventario.compras.create', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric|min:0',
            'fecha_compra' => 'nullable|date'
        ]);

        $compra = CompraInsumo::create([
            'proveedor_id' => $request->proveedor_id,
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'fecha_compra' => $request->fecha_compra ?? now(),
            'registrado_por' => auth()->user()->id_usuario,
            'tela_id' => $request->tela_id ?? null,
            'cantidad' => $request->cantidad ?? 0,
        ]);

        $this->bitacoraService->registrarActividad('CREATE', 'INVENTARIO', "Compra registrada: {$compra->monto} por proveedor {$compra->proveedor->nombre}", null, $compra->toArray());

        // Si la compra aumenta stock de tela, actualizar stock
        if ($compra->tela_id && $compra->cantidad > 0) {
            $tela = \App\Models\Tela::find($compra->tela_id);
            if ($tela) {
                $tela->reponer($compra->cantidad);
                $this->bitacoraService->registrarActividad('UPDATE', 'INVENTARIO', "Stock actualizado por compra: {$compra->cantidad} {$tela->unidad} para {$tela->nombre}", null, $tela->toArray());
            }
        }

        return redirect()->route('compras.index')->with('success', 'Compra registrada correctamente.');
    }

    public function historialPorProveedor($proveedorId)
    {
        $proveedor = Proveedor::findOrFail($proveedorId);
        $compras = CompraInsumo::where('proveedor_id', $proveedor->id)->orderBy('fecha_compra', 'desc')->paginate(20);
        return view('inventario.compras.historial', compact('proveedor', 'compras'));
    }

    public function auditarUltimaSemana()
    {
        $lastWeek = now()->subWeek();
        $compras = CompraInsumo::where('fecha_compra', '>=', $lastWeek)->orderBy('fecha_compra', 'desc')->with('proveedor')->get();
        $bitacoras = \App\Models\Bitacora::where('modulo', 'INVENTARIO')->where('created_at', '>=', $lastWeek)->orderBy('created_at', 'desc')->get();

        return view('inventario.compras.auditar', compact('compras', 'bitacoras'));
    }
}
