<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

class HandleCsrfErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (TokenMismatchException $e) {
            // Manejar error 419 - Token CSRF inválido
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu sesión ha expirado. Por favor recarga la página.',
                    'error' => 'token_mismatch'
                ], 419);
            }

            // Para requests normales, redirigir con mensaje
            $redirectRoute = $this->getRedirectRoute($request);
            
            return redirect()->route($redirectRoute)
                ->with('error', 'Tu sesión ha expirado. Por favor intenta nuevamente.')
                ->withInput($request->except(['_token', '_method']));
        }
    }

    /**
     * Determinar la ruta de redirección basada en la URL actual
     */
    private function getRedirectRoute(Request $request): string
    {
        $path = $request->path();
        
        if (str_contains($path, 'hacer-pedido')) {
            return 'pedidos.cliente-crear';
        }
        
        if (str_contains($path, 'crear-pedido-cliente')) {
            return 'pedidos.empleado-crear';
        }
        
        if (str_contains($path, 'pedidos')) {
            return 'pedidos.index';
        }
        
        return 'dashboard';
    }
}
