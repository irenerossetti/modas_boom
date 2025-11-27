<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Services\BitacoraService;

class ProveedorController extends Controller
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    public function index()
    {
        $proveedores = Proveedor::orderBy('nombre')->paginate(20);
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email'
        ]);

        $proveedor = Proveedor::create($request->only(['nombre','contacto','telefono','email']));

        $this->bitacoraService->registrarActividad('CREATE', 'INVENTARIO', "Proveedor registrado: {$proveedor->nombre}", null, $proveedor->toArray());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado correctamente.');
    }

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email'
        ]);

        $proveedor->update($request->only(['nombre','contacto','telefono','email']));

        $this->bitacoraService->registrarActividad('UPDATE', 'INVENTARIO', "Proveedor actualizado: {$proveedor->nombre}", null, $proveedor->toArray());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();

        $this->bitacoraService->registrarActividad('DELETE', 'INVENTARIO', "Proveedor eliminado: {$proveedor->nombre}", null, $proveedor->toArray());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado correctamente.');
    }
}
