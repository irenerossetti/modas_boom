<?php

namespace App\Http\Controllers;

use App\Models\User; // <-- ¡MUY IMPORTANTE! Asegúrate que usa 'User'
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UsuarioController extends Controller
{
    /**
     * Muestra una lista de todos los usuarios.
     */
    public function index()
    {
        $usuarios = User::with('rol')->latest()->paginate(10);
        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        $roles = Rol::all();
        return view('usuarios.create', compact('roles'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol_id' => ['required', 'exists:rols,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function edit(User $usuario) // <-- Fíjate que aquí también dice 'User'
    {
        $roles = Rol::all();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualiza la información de un usuario.
     */
    public function update(Request $request, User $usuario) // <-- Y aquí
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$usuario->id],
            'rol_id' => ['required', 'exists:rols,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $data = $request->only('name', 'email', 'rol_id');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario de la base de datos.
     */
    public function destroy(User $usuario) // <-- Y aquí también
    {
        // Opcional: Evitar que un admin se elimine a sí mismo
        if (auth()->user()->id === $usuario->id) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}