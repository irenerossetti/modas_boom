<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Rol::with('usuarios')->get();
        $isReadOnly = !auth()->user() || auth()->user()->id_rol != 1;
        return view('roles.index', compact('roles', 'isReadOnly'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:rol',
            'descripcion' => 'nullable|string|max:500',
            'habilitado' => 'boolean',
        ]);

        Rol::create($request->all());

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rol $rol)
    {
        $rol->load('usuarios');
        $isReadOnly = !auth()->user() || auth()->user()->id_rol != 1;
        return view('roles.show', compact('rol', 'isReadOnly'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rol $rol)
    {
        // Proteger el rol de Administrador con mensaje divertido
        if ($rol->nombre === 'Administrador' || $rol->id_rol == 1) {
            return redirect()->route('roles.index')
                ->with('error', '😂 ¡Jajaja no puedes tocar al admin! El rol de Administrador está protegido contra modificaciones.');
        }
        
        return view('roles.edit', compact('rol'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rol $rol)
    {
        // Proteger el rol de Administrador
        if ($rol->nombre === 'Administrador' || $rol->id_rol == 1) {
            return redirect()->route('roles.index')
                ->with('error', '🚫 ¡Nice try! Pero el rol de Administrador no se puede modificar. Es sagrado. 😎');
        }
        
        $request->validate([
            'nombre' => 'required|string|max:255|unique:rol,nombre,' . $rol->id_rol . ',id_rol',
            'descripcion' => 'nullable|string|max:500',
            'habilitado' => 'boolean',
        ]);

        $rol->update($request->all());

        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rol $rol)
    {
        // Proteger el rol de Administrador
        if ($rol->nombre === 'Administrador' || $rol->id_rol == 1) {
            return redirect()->route('roles.index')
                ->with('error', '💀 ¡Eliminar al admin? ¡Estás loco! Ese rol es intocable, mi amigo. 😂');
        }
        
        // Verificar que no haya usuarios con este rol antes de eliminar
        if ($rol->usuarios()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        $rol->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
    }
}
