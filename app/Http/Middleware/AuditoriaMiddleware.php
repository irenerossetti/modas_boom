<?php

namespace App\Http\Middleware;

use App\Services\BitacoraService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditoriaMiddleware
{
    protected $bitacoraService;

    public function __construct(BitacoraService $bitacoraService)
    {
        $this->bitacoraService = $bitacoraService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo registrar si el usuario está autenticado y la respuesta es exitosa
        if (Auth::check() && $response->getStatusCode() < 400) {
            $this->registrarActividad($request, $response);
        }

        return $response;
    }

    /**
     * Registrar actividad basada en la ruta y método HTTP
     */
    private function registrarActividad(Request $request, Response $response): void
    {
        try {
            $ruta = $request->route();
            if (!$ruta) return;

            $rutaNombre = $ruta->getName();
            $metodo = $request->method();
            $parametros = $ruta->parameters();

            // Mapear rutas a acciones y módulos
            $accionModulo = $this->mapearRutaAAccionModulo($rutaNombre, $metodo, $parametros);
            
            if (!$accionModulo) return;

            [$accion, $modulo, $descripcion] = $accionModulo;

            // Obtener datos del request para operaciones de creación/actualización
            $datosNuevos = null;
            if (in_array($metodo, ['POST', 'PUT', 'PATCH']) && $request->isMethod('POST')) {
                $datosNuevos = $this->sanitizarDatosRequest($request->all());
            }

            $this->bitacoraService->registrarActividad(
                $accion,
                $modulo,
                $descripcion,
                null, // datos anteriores se manejan en los listeners de modelos
                $datosNuevos,
                Auth::id(),
                $request->ip(),
                $request->userAgent()
            );
        } catch (\Exception $e) {
            // Log silencioso para no interrumpir el flujo
            \Log::error('Error en AuditoriaMiddleware: ' . $e->getMessage());
        }
    }

    /**
     * Mapear nombre de ruta a acción y módulo
     */
    private function mapearRutaAAccionModulo(string $rutaNombre = null, string $metodo, array $parametros): ?array
    {
        if (!$rutaNombre) return null;

        // Mapeo de rutas específicas
        $mapeoRutas = [
            // Usuarios
            'users.index' => ['VIEW', 'USUARIOS', 'Consultó lista de usuarios'],
            'users.create' => ['VIEW', 'USUARIOS', 'Accedió al formulario de creación de usuario'],
            'users.store' => ['CREATE', 'USUARIOS', 'Creó un nuevo usuario'],
            'users.show' => ['VIEW', 'USUARIOS', 'Consultó detalles de usuario'],
            'users.edit' => ['VIEW', 'USUARIOS', 'Accedió al formulario de edición de usuario'],
            'users.update' => ['UPDATE', 'USUARIOS', 'Actualizó datos de usuario'],
            'users.destroy' => ['DELETE', 'USUARIOS', 'Eliminó un usuario'],

            // Clientes
            'clientes.index' => ['VIEW', 'CLIENTES', 'Consultó lista de clientes'],
            'clientes.create' => ['VIEW', 'CLIENTES', 'Accedió al formulario de creación de cliente'],
            'clientes.store' => ['CREATE', 'CLIENTES', 'Creó un nuevo cliente'],
            'clientes.show' => ['VIEW', 'CLIENTES', 'Consultó detalles de cliente'],
            'clientes.edit' => ['VIEW', 'CLIENTES', 'Accedió al formulario de edición de cliente'],
            'clientes.update' => ['UPDATE', 'CLIENTES', 'Actualizó datos de cliente'],
            'clientes.destroy' => ['DELETE', 'CLIENTES', 'Eliminó un cliente'],

            // Roles
            'roles.index' => ['VIEW', 'ROLES', 'Consultó lista de roles'],
            'roles.create' => ['VIEW', 'ROLES', 'Accedió al formulario de creación de rol'],
            'roles.store' => ['CREATE', 'ROLES', 'Creó un nuevo rol'],
            'roles.show' => ['VIEW', 'ROLES', 'Consultó detalles de rol'],
            'roles.edit' => ['VIEW', 'ROLES', 'Accedió al formulario de edición de rol'],
            'roles.update' => ['UPDATE', 'ROLES', 'Actualizó datos de rol'],
            'roles.destroy' => ['DELETE', 'ROLES', 'Eliminó un rol'],

            // Dashboard
            'dashboard' => ['VIEW', 'SISTEMA', 'Accedió al dashboard administrativo'],
            'empleado.dashboard' => ['VIEW', 'SISTEMA', 'Accedió al dashboard de empleado'],

            // Perfil
            'profile.edit' => ['VIEW', 'USUARIOS', 'Accedió a la edición de perfil'],
            'profile.update' => ['UPDATE', 'USUARIOS', 'Actualizó su perfil'],
            'profile.destroy' => ['DELETE', 'USUARIOS', 'Eliminó su cuenta'],

            // Bitácora (ya se registra en el controlador)
            'bitacora.index' => null, // Se registra en el controlador
            'bitacora.exportar' => null, // Se registra en el controlador
        ];

        return $mapeoRutas[$rutaNombre] ?? null;
    }

    /**
     * Sanitizar datos del request
     */
    private function sanitizarDatosRequest(array $datos): array
    {
        // Remover campos sensibles
        $camposSensibles = ['password', 'password_confirmation', '_token', '_method'];
        
        foreach ($camposSensibles as $campo) {
            unset($datos[$campo]);
        }

        // Limitar tamaño de datos
        $datosString = json_encode($datos);
        if (strlen($datosString) > 32768) { // 32KB límite
            return ['mensaje' => 'Datos demasiado grandes para registrar'];
        }

        return $datos;
    }
}
