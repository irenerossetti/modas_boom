<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoginAttemptThrottle
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'login_attempts_' . $request->ip();
        $maxAttempts = 5; // Máximo número de intentos
        $decayMinutes = 15; // Tiempo de bloqueo en minutos

        // Si el usuario ya está bloqueado
        if (Cache::has($key . '_blocked')) {
            $remainingTime = Cache::get($key . '_blocked');
            return response()->json([
                'message' => 'Demasiados intentos fallidos. Intente nuevamente en ' . $remainingTime . ' minutos.',
                'blocked' => true
            ], 429);
        }

        $response = $next($request);

        // Si el login falló (código 422 para validación fallida)
        if ($response->getStatusCode() === 422 && $request->is('login')) {
            $attempts = Cache::get($key, 0) + 1;
            Cache::put($key, $attempts, now()->addMinutes($decayMinutes));

            // Si supera el máximo de intentos, bloquear
            if ($attempts >= $maxAttempts) {
                Cache::put($key . '_blocked', $decayMinutes, now()->addMinutes($decayMinutes));
                Cache::forget($key); // Limpiar contador de intentos

                return response()->json([
                    'message' => 'Cuenta bloqueada por ' . $decayMinutes . ' minutos debido a múltiples intentos fallidos.',
                    'blocked' => true
                ], 429);
            }

            // Agregar información de intentos restantes
            $remainingAttempts = $maxAttempts - $attempts;
            $responseData = json_decode($response->getContent(), true);
            $responseData['remaining_attempts'] = $remainingAttempts;
            $response->setContent(json_encode($responseData));
        }

        // Si el login fue exitoso, limpiar el contador
        if ($response->getStatusCode() === 200 && $request->is('login') && Auth::check()) {
            Cache::forget($key);
            Cache::forget($key . '_blocked');
        }

        return $response;
    }
}