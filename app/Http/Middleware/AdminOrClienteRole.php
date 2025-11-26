<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrClienteRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Solo administradores (1) o clientes (3)
        if (!in_array($user->id_rol, [1, 3])) {
            abort(403, 'No tienes permisos para acceder a esta funcionalidad. Solo administradores o clientes pueden continuar.');
        }

        return $next($request);
    }
}
