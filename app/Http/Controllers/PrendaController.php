<?php

namespace App\Http\Controllers;

use App\Models\Prenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            // El parámetro puede llegar como '1' / '0' o 'true' / 'false' desde la query string.
            // Convertimos de forma segura a boolean para evitar comparaciones tipo boolean = integer
            // que fallan en Postgres (ej: "activo" = 1).
            $activo = filter_var($request->activo, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            // Si la conversión no produce boolean (null), ignoramos el filtro.
            if (!is_null($activo)) {
                // Evitar que Postgres construya comparaciones tipo boolean = integer ("activo" = 1)
                // Forzar la comparación booleano explícita en SQL
                if ($activo === true) {
                    $query->whereRaw('"activo" = true');
                } else {
                    $query->whereRaw('"activo" = false');
                }
            }
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
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:100',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'colores' => 'nullable|array',
            'colores.*' => 'string|max:50',
            'tallas' => 'nullable|array',
            'tallas.*' => 'string|max:10',
            'stock' => 'required|integer|min:0',
            'activo' => 'boolean'
        ]);

        // Use the validated data only (exclude _token/_method and any other unexpected fields)
        $data = $validated;
        
        // Procesar imagen si se subió
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
            $imagen->move(public_path('images/prendas'), $nombreImagen);
            $data['imagen'] = 'images/prendas/' . $nombreImagen;
        }

        // Filtrar colores vacíos
        if (isset($data['colores'])) {
            $data['colores'] = array_filter($data['colores'], function($color) {
                return !empty(trim($color));
            });
        }

        // Asegurar que activo sea booleano para almacenar correctamente en DB
        $data['activo'] = $request->boolean('activo');

        // Convertir campos JSON a string para insert con query builder (Postgres)
        $insertData = $data;
        if (isset($insertData['colores']) && is_array($insertData['colores'])) {
            $insertData['colores'] = json_encode(array_values($insertData['colores']));
        }
        if (isset($insertData['tallas']) && is_array($insertData['tallas'])) {
            $insertData['tallas'] = json_encode(array_values($insertData['tallas']));
        }

        // More robust driver check (in case driver name appears differently) and use DB::table
        if (Str::contains(strtolower(DB::connection()->getDriverName()), 'pg')) {
            // Use literal boolean expression for PG
            $insertData['activo'] = DB::raw($insertData['activo'] ? 'true' : 'false');
            $insertData['created_at'] = now();
            $insertData['updated_at'] = now();

            // Prevent unexpected fields (e.g. _token, _method) from being passed to DB::table
            $allowedColumns = array_merge((new Prenda())->getFillable(), ['created_at', 'updated_at']);
            $insertData = array_intersect_key($insertData, array_flip($allowedColumns));

            DB::table('prendas')->insert($insertData);
        } else {
            Prenda::create($data);
        }

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
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:100',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'colores' => 'nullable|array',
            'colores.*' => 'string|max:50',
            'tallas' => 'nullable|array',
            'tallas.*' => 'string|max:10',
            'stock' => 'required|integer|min:0',
            'activo' => 'boolean'
        ]);

        // Use the validated data only (exclude _token/_method and any other unexpected fields)
        $data = $validated;
        
        // Procesar imagen si se subió una nueva
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($prenda->imagen && file_exists(public_path($prenda->imagen))) {
                unlink(public_path($prenda->imagen));
            }
            
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
            $imagen->move(public_path('images/prendas'), $nombreImagen);
            $data['imagen'] = 'images/prendas/' . $nombreImagen;
        } else {
            // Mantener la imagen actual
            unset($data['imagen']);
        }

        // Filtrar colores vacíos
        if (isset($data['colores'])) {
            $data['colores'] = array_filter($data['colores'], function($color) {
                return !empty(trim($color));
            });
        }

        // Asegurar que activo sea booleano para almacenar correctamente en DB
        $data['activo'] = $request->boolean('activo');

        // Preparar actualización para Postgres con DB::raw y encoding JSON
        $updateData = $data;
        if (isset($updateData['colores']) && is_array($updateData['colores'])) {
            $updateData['colores'] = json_encode(array_values($updateData['colores']));
        }
        if (isset($updateData['tallas']) && is_array($updateData['tallas'])) {
            $updateData['tallas'] = json_encode(array_values($updateData['tallas']));
        }

        if (Str::contains(strtolower(DB::connection()->getDriverName()), 'pg')) {
            $updateData['activo'] = DB::raw($updateData['activo'] ? 'true' : 'false');
            $updateData['updated_at'] = now();

            // Prevent unexpected fields (e.g. _token, _method) from being passed to DB::table
            $allowedColumns = array_merge((new Prenda())->getFillable(), ['updated_at']);
            $updateData = array_intersect_key($updateData, array_flip($allowedColumns));

            DB::table('prendas')->where('id', $prenda->id)->update($updateData);
        } else {
            $prenda->update($data);
        }

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

    /**
     * Mostrar ranking de productos más vendidos (CU27)
     */
    public function ranking(Request $request)
    {
        // Filtrar por fecha opcionalmente
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        $query = DB::table('pedido_prenda as pp')
            ->join('pedido as p', 'pp.pedido_id', '=', 'p.id_pedido')
            ->join('prendas as pr', 'pp.prenda_id', '=', 'pr.id')
            ->select('pp.prenda_id', 'pr.nombre', 'pr.categoria', DB::raw('SUM(pp.cantidad) as total_vendidos'))
            ->whereNotIn('p.estado', ['Cancelado']);

        if ($fechaDesde) {
            $query->whereDate('p.created_at', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate('p.created_at', '<=', $fechaHasta);
        }

        $ranking = $query->groupBy('pp.prenda_id', 'pr.nombre', 'pr.categoria')
            ->orderByDesc('total_vendidos')
            ->limit(50)
            ->get();

        // Ofrecer tanto vista HTML como JSON
        if ($request->wantsJson()) {
            return response()->json($ranking);
        }

        return view('prendas.ranking', compact('ranking'));
    }
}
