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
            'telefono' => 'nullable|string|max:15|unique:usuario',
            'direccion' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:usuario',
            'password' => 'required|string|min:8|confirmed',
            'habilitado' => 'boolean'
        ]);

        $user = User::create([
            'id_rol' => $request->id_rol,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'habilitado' => $request->has('habilitado') ? 1 : 0,
        ]);

        // Si el rol es Cliente (id_rol = 3), crear cliente automáticamente
        if ($request->id_rol == 3) {
            \App\Models\Cliente::create([
                'id_usuario' => $user->id_usuario,
                'nombre' => $request->nombre,
                'apellido' => '',
                'ci_nit' => '',
                'telefono' => $request->telefono,
                'email' => $request->email,
                'direccion' => $request->direccion,
            ]);
        }

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
            'telefono' => 'nullable|string|max:15|unique:usuario,telefono,' . $id . ',id_usuario',
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

        // Si el rol cambió a Cliente (id_rol = 3), crear cliente si no existe
        if ($request->id_rol == 3 && !\App\Models\Cliente::where('id_usuario', $user->id_usuario)->exists()) {
            \App\Models\Cliente::create([
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->nombre,
                'apellido' => '', // Puedes agregar campo apellido en el form si quieres
                'ci_nit' => '',
                'telefono' => $user->telefono,
                'email' => $user->email,
                'direccion' => $user->direccion,
            ]);
        }

        // Si el rol cambió de Cliente a otro, eliminar el cliente si existe
        if ($request->id_rol != 3 && \App\Models\Cliente::where('id_usuario', $user->id_usuario)->exists()) {
            \App\Models\Cliente::where('id_usuario', $user->id_usuario)->delete();
        }

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
