<?php

namespace App\Http\Controllers;

use App\Models\Prenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PrendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Prenda::query();

        // Filtros
        if ($request->categoria) {
            $query->byCategoria($request->categoria);
        }

        if ($request->busqueda) {
            $query->buscar($request->busqueda);
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        $prendas = $query->orderBy('categoria')->orderBy('nombre')->paginate(12);
        $categorias = Prenda::getCategorias();

        return view('prendas.index', compact('prendas', 'categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('prendas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:100',
            'imagen' => 'nullable|string|max:500',
            'colores' => 'nullable|array',
            'tallas' => 'nullable|array',
            'stock' => 'required|integer|min:0',
            'activo' => 'boolean'
        ]);

        Prenda::create($request->all());

        // Limpiar cache
        Cache::forget('productos_catalogo_db');

        return redirect()->route('prendas.index')
            ->with('success', 'Prenda creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prenda $prenda)
    {
        return view('prendas.show', compact('prenda'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prenda $prenda)
    {
        return view('prendas.edit', compact('prenda'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prenda $prenda)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:100',
            'imagen' => 'nullable|string|max:500',
            'colores' => 'nullable|array',
            'tallas' => 'nullable|array',
            'stock' => 'required|integer|min:0',
            'activo' => 'boolean'
        ]);

        $prenda->update($request->all());

        // Limpiar cache
        Cache::forget('productos_catalogo_db');

        return redirect()->route('prendas.index')
            ->with('success', 'Prenda actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prenda $prenda)
    {
        $prenda->delete();

        // Limpiar cache
        Cache::forget('productos_catalogo_db');

        return redirect()->route('prendas.index')
            ->with('success', 'Prenda eliminada exitosamente.');
    }
}
