<?php

namespace App\Http\Controllers;

use App\Models\User; // <-- ¡Importa el modelo User!
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos todos los usuarios de la tabla 'usuario'
        $users = User::all();

        // Pasamos los usuarios a la vista
        return view('users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = \App\Models\Rol::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_rol' => 'required|exists:rol,id_rol',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:15',
            'direccion' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:usuario',
            'password' => 'required|string|min:8|confirmed',
            'habilitado' => 'boolean'
        ]);

        User::create([
            'id_rol' => $request->id_rol,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'habilitado' => $request->has('habilitado') ? 1 : 0,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('rol')->findOrFail($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = \App\Models\Rol::all();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'id_rol' => 'required|exists:rol,id_rol',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:15',
            'direccion' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:usuario,email,' . $id . ',id_usuario',
            'password' => 'nullable|string|min:8|confirmed',
            'habilitado' => 'boolean'
        ]);

        $data = [
            'id_rol' => $request->id_rol,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'email' => $request->email,
            'habilitado' => $request->has('habilitado') ? 1 : 0,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Evitar que un usuario se elimine a sí mismo
        if ($user->id_usuario == auth()->id()) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
