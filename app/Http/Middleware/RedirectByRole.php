<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $currentRoute = $request->route()->getName();
            
            // Prevenir que los clientes accedan al dashboard de admin
            if ($user->id_rol == 3 && $currentRoute == 'dashboard') {
                return redirect()->route('cliente.dashboard');
            }
            
            // Prevenir que los empleados accedan al dashboard de admin sin permisos
            if ($user->id_rol == 2 && $currentRoute == 'dashboard') {
                return redirect()->route('pedidos.index');
            }
            
            // Prevenir que admin/empleados accedan al dashboard de cliente
            if (in_array($user->id_rol, [1, 2]) && $currentRoute == 'cliente.dashboard') {
                if ($user->id_rol == 1) {
                    return redirect()->route('dashboard');
                } else {
                    return redirect()->route('pedidos.index');
                }
            }
        }
        
        return $next($request);
    }
}
