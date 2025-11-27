<?php

namespace App\Http\Controllers;

use App\Models\Tela;
use App\Services\BitacoraService;
use Illuminate\Http\Request;

class TelaController extends Controller
{
    protected $bitacoraService;
    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    public function index()
    {
        $telas = Tela::orderBy('nombre')->paginate(20);
        return view('telas.index', compact('telas'));
    }

    public function create()
    {
        return view('telas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'stock' => 'required|numeric|min:0',
            'unidad' => 'nullable|string|max:10',
            'stock_minimo' => 'nullable|numeric|min:0'
        ]);

        $tela = Tela::create($request->only(['nombre','descripcion','stock','unidad','stock_minimo']));

        $this->bitacoraService->registrarActividad(
            'CREATE',
            'INVENTARIO',
            "Administrador registró tela: {$tela->nombre} ({$tela->stock} {$tela->unidad})",
            null,
            $tela->toArray()
        );

        return redirect()->route('telas.index')->with('success', 'Tela registrada correctamente.');
    }

    // Show edit form
    public function edit($id)
    {
        $tela = Tela::findOrFail($id);
        return view('telas.edit', compact('tela'));
    }

    public function update(Request $request, $id)
    {
        $tela = Tela::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'stock' => 'required|numeric|min:0',
            'unidad' => 'nullable|string|max:10',
            'stock_minimo' => 'nullable|numeric|min:0'
        ]);
        $tela->update($request->only(['nombre','descripcion','stock','unidad','stock_minimo']));
        $this->bitacoraService->registrarActividad('UPDATE','INVENTARIO', "Tela actualizada: {$tela->nombre}", null, $tela->toArray());

        return redirect()->route('telas.index')->with('success','Tela actualizada');
    }

    // Admin: register consumption after production
    public function consumir(Request $request, $id)
    {
        $tela = Tela::findOrFail($id);
        $request->validate(['cantidad' => 'required|numeric|min:0.01']);
        $cantidad = $request->cantidad;
        $success = $tela->consumir($cantidad);
        if (!$success) {
            return redirect()->back()->with('error','Stock insuficiente para este consumo');
        }

        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'INVENTARIO',
            "Consumo de tela: {$cantidad} {$tela->unidad} sobre {$tela->nombre}",
            null,
            $tela->toArray()
        );

        if ($tela->isLowStock()) {
            $this->bitacoraService->registrarActividad('ALERTA','INVENTARIO', "Stock bajo para la tela {$tela->nombre} - {$tela->stock} {$tela->unidad}", null, $tela->toArray());
        }

        return redirect()->back()->with('success','Consumo registrado y stock actualizado.');
    }

}
