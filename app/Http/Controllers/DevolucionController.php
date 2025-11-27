<?php

namespace App\Http\Controllers;

use App\Models\DevolucionPrenda;
use App\Models\Pedido;
use App\Models\Prenda;
use App\Services\BitacoraService;
use Illuminate\Http\Request;

class DevolucionController extends Controller
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    /**
     * Lista todas las devoluciones
     */
    public function index(Request $request)
    {
        $devoluciones = DevolucionPrenda::with(['pedido', 'prenda', 'registradoPor'])->orderBy('created_at', 'desc')->paginate(25);
        return view('devoluciones.index', compact('devoluciones'));
    }

    /**
     * Muestra el formulario para registrar una devolucion para un pedido
     */
    public function create($pedidoId)
    {
        $pedido = Pedido::with('prendas')->findOrFail($pedidoId);
        return view('devoluciones.create', compact('pedido'));
    }

    /**
     * Almacena una devolucion
     */
    public function store(Request $request, $pedidoId)
    {
        $pedido = Pedido::with('prendas')->findOrFail($pedidoId);

        $request->validate([
            'prenda_id' => 'required|exists:prendas,id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:1000'
        ]);

        // Verificar que la prenda pertenezca al pedido y obtenga la cantidad máxima posible de devolución
        $pivot = $pedido->prendas->firstWhere('id', $request->prenda_id);
        if (!$pivot) {
            return redirect()->route('pedidos.show', $pedido->id_pedido)->with('error', 'La prenda seleccionada no pertenece a este pedido.');
        }

        $maxCantidad = $pivot->pivot->cantidad;
        if ($request->cantidad > $maxCantidad) {
            return redirect()->back()->withInput()->with('error', 'La cantidad de devolución no puede ser mayor a la cantidad vendida en el pedido.');
        }

        $devolucion = DevolucionPrenda::create([
            'id_pedido' => $pedido->id_pedido,
            'id_prenda' => $request->prenda_id,
            'cantidad' => $request->cantidad,
            'motivo' => $request->motivo,
            'registrado_por' => auth()->id(),
        ]);

        // Restaurar stock de la prenda (teniendo en cuenta que la pivot cantidad usa unidades; stock se maneja en docenas)
        $prenda = Prenda::find($request->prenda_id);
        if ($prenda) {
            // Convertimos unidades devueltas a docenas
            $cantidadDocenas = $request->cantidad / 12;
            $prenda->restaurarStock($cantidadDocenas);
        }

        // Registrar en bitácora (agregar información legible y módulo PEDIDOS para que aparezca en el historial del pedido)
        $mensaje = auth()->user()->nombre . " registró devolución de {$devolucion->cantidad} unidad" . ($devolucion->cantidad > 1 ? 'es' : '') . " de la prenda '" . ($prenda->nombre ?? 'Desconocida') . "' en el pedido #{$pedido->id_pedido}";
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PEDIDOS',
            $mensaje,
            null,
            array_merge($devolucion->toArray(), ['prenda_nombre' => $prenda->nombre ?? null, 'pedido_id' => $pedido->id_pedido])
        );

        return redirect()->route('pedidos.show', $pedido->id_pedido)->with('success', 'La devolución ha sido registrada correctamente.');
    }

    /**
     * Mostrar detalles de una devolución
     */
    public function show($id)
    {
        $devolucion = DevolucionPrenda::with(['pedido', 'prenda', 'registradoPor'])->findOrFail($id);
        return view('devoluciones.show', compact('devolucion'));
    }
}
