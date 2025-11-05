<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendedorRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Verificar que el usuario tenga rol de vendedor (empleado - id_rol = 2) o administrador (id_rol = 1)
        if (!in_array($user->id_rol, [1, 2])) {
            abort(403, 'No tienes permisos para acceder a esta funcionalidad. Solo vendedores pueden reprogramar entregas.');
        }

        return $next($request);
    }
}
