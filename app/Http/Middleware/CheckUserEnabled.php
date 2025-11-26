<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckUserEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->habilitado) {
            Log::info('CheckUserEnabled - User disabled, logging out and redirecting', ['user_id' => Auth::id()]);
            Auth::logout();

            return redirect('/login')->withErrors([
                'email' => 'Su cuenta ha sido deshabilitada. Contacte al administrador.'
            ]);
        }

        return $next($request);
    }
}