<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'ci_nit' => ['required', 'string', 'max:20', 'unique:clientes'],
            'telefono' => ['nullable', 'string', 'max:15', 'unique:usuario', 'unique:clientes'],
            'direccion' => ['nullable', 'string'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'id_rol' => 3, // Rol Cliente por defecto
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Crear cliente asociado
        \App\Models\Cliente::create([
            'id_usuario' => $user->id_usuario,
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'ci_nit' => $request->ci_nit,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'direccion' => $request->direccion,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirigir según rol (para cliente, a landing)
        return redirect('/');
    }
}
