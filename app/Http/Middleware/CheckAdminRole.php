<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (Auth::user()->id_rol != 1) {
            Log::info('CheckAdminRole - redirecting non-admin', ['user_id' => Auth::id(), 'id_rol' => Auth::user()->id_rol]);
            // If employee, redirect to employee dashboard
            if (Auth::user()->id_rol == 2) {
                return redirect('/empleado-dashboard');
            }
            // If client, redirect to client dashboard
            if (Auth::user()->id_rol == 3) {
                return redirect('/cliente/dashboard');
            }
            // Default redirect to home
            return redirect('/');
        }

        return $next($request);
    }
}
