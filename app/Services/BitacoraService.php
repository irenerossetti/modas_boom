<?php

namespace App\Services;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BitacoraService
{
    /**
     * Registrar una actividad en la bitácora
     */
    public function registrarActividad(
        string $accion,
        string $modulo,
        string $descripcion,
        array $datosAnteriores = null,
        array $datosNuevos = null,
        int $usuarioId = null,
        string $ipAddress = null,
        string $userAgent = null
    ): bool {
        try {
            // Obtener datos del usuario actual si no se proporciona
            if (!$usuarioId && Auth::check()) {
                $usuarioId = Auth::id();
            }

            // Obtener IP y User Agent del request actual si no se proporcionan
            if (!$ipAddress || !$userAgent) {
                $request = request();
                if ($request) {
                    $ipAddress = $ipAddress ?: $request->ip();
                    $userAgent = $userAgent ?: $request->userAgent();
                }
            }

            // Sanitizar datos
            $datosAnteriores = $this->sanitizarDatos($datosAnteriores);
            $datosNuevos = $this->sanitizarDatos($datosNuevos);

            // Crear registro en bitácora
            Bitacora::create([
                'id_usuario' => $usuarioId,
                'accion' => $accion,
                'modulo' => $modulo,
                'descripcion' => $descripcion,
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $datosNuevos,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return true;
        } catch (\Exception $e) {
            // Log del error sin interrumpir el flujo normal
            Log::error('Error al registrar actividad en bitácora: ' . $e->getMessage(), [
                'accion' => $accion,
                'modulo' => $modulo,
                'descripcion' => $descripcion,
                'usuario_id' => $usuarioId,
            ]);
            
            return false;
        }
    }

    /**
     * Obtener registros filtrados de la bitácora
     */
    public function obtenerRegistrosFiltrados(array $filtros = [], int $perPage = 20)
    {
        $query = Bitacora::with('usuario')
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['id_usuario'])) {
            $query->where('id_usuario', $filtros['id_usuario']);
        }

        if (!empty($filtros['accion'])) {
            $query->where('accion', $filtros['accion']);
        }

        if (!empty($filtros['modulo'])) {
            $query->where('modulo', $filtros['modulo']);
        }

        if (!empty($filtros['busqueda'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('descripcion', 'like', '%' . $filtros['busqueda'] . '%')
                  ->orWhereHas('usuario', function ($userQuery) use ($filtros) {
                      $userQuery->where('nombre', 'like', '%' . $filtros['busqueda'] . '%');
                  });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Registrar login de usuario
     */
    public function registrarLogin(int $usuarioId, string $ipAddress = null, string $userAgent = null): bool
    {
        $usuario = User::find($usuarioId);
        $nombreUsuario = $usuario ? $usuario->nombre : 'Usuario desconocido';

        return $this->registrarActividad(
            'LOGIN',
            'AUTH',
            "Usuario {$nombreUsuario} inició sesión",
            null,
            ['usuario_id' => $usuarioId, 'nombre' => $nombreUsuario],
            $usuarioId,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Registrar logout de usuario
     */
    public function registrarLogout(int $usuarioId, string $ipAddress = null, string $userAgent = null): bool
    {
        $usuario = User::find($usuarioId);
        $nombreUsuario = $usuario ? $usuario->nombre : 'Usuario desconocido';

        return $this->registrarActividad(
            'LOGOUT',
            'AUTH',
            "Usuario {$nombreUsuario} cerró sesión",
            null,
            ['usuario_id' => $usuarioId, 'nombre' => $nombreUsuario],
            $usuarioId,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Registrar creación de modelo
     */
    public function registrarCreacion(string $modelo, array $datos, int $usuarioId = null): bool
    {
        $modulo = $this->obtenerModuloPorModelo($modelo);
        
        return $this->registrarActividad(
            'CREATE',
            $modulo,
            "Se creó un nuevo registro en {$modelo}",
            null,
            $datos,
            $usuarioId
        );
    }

    /**
     * Registrar actualización de modelo
     */
    public function registrarActualizacion(string $modelo, array $datosAnteriores, array $datosNuevos, int $usuarioId = null): bool
    {
        $modulo = $this->obtenerModuloPorModelo($modelo);
        
        return $this->registrarActividad(
            'UPDATE',
            $modulo,
            "Se actualizó un registro en {$modelo}",
            $datosAnteriores,
            $datosNuevos,
            $usuarioId
        );
    }

    /**
     * Registrar eliminación de modelo
     */
    public function registrarEliminacion(string $modelo, array $datos, int $usuarioId = null): bool
    {
        $modulo = $this->obtenerModuloPorModelo($modelo);
        
        return $this->registrarActividad(
            'DELETE',
            $modulo,
            "Se eliminó un registro en {$modelo}",
            $datos,
            null,
            $usuarioId
        );
    }

    /**
     * Obtener usuarios para filtros
     */
    public function obtenerUsuariosParaFiltros()
    {
        return User::select('id_usuario', 'nombre')
            ->where('habilitado', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener acciones disponibles
     */
    public function obtenerAccionesDisponibles(): array
    {
        return [
            'LOGIN' => 'Inicio de sesión',
            'LOGOUT' => 'Cierre de sesión',
            'CREATE' => 'Crear',
            'UPDATE' => 'Actualizar',
            'DELETE' => 'Eliminar',
            'VIEW' => 'Visualizar',
        ];
    }

    /**
     * Obtener módulos disponibles
     */
    public function obtenerModulosDisponibles(): array
    {
        return [
            'AUTH' => 'Autenticación',
            'USUARIOS' => 'Usuarios',
            'CLIENTES' => 'Clientes',
            'ROLES' => 'Roles',
            'PEDIDOS' => 'Pedidos',
            'BITACORA' => 'Bitácora',
        ];
    }

    /**
     * Sanitizar datos para almacenamiento seguro
     */
    private function sanitizarDatos(array $datos = null): ?array
    {
        if (!$datos) {
            return null;
        }

        // Remover campos sensibles
        $camposSensibles = ['password', 'remember_token', 'api_token'];
        
        foreach ($camposSensibles as $campo) {
            unset($datos[$campo]);
        }

        // Limitar tamaño de datos para evitar registros muy grandes
        $datosString = json_encode($datos);
        if (strlen($datosString) > 65535) { // Límite de TEXT en MySQL
            return ['mensaje' => 'Datos demasiado grandes para almacenar'];
        }

        return $datos;
    }

    /**
     * Obtener módulo basado en el nombre del modelo
     */
    private function obtenerModuloPorModelo(string $modelo): string
    {
        $mapeoModelos = [
            'User' => 'USUARIOS',
            'Usuario' => 'USUARIOS',
            'Cliente' => 'CLIENTES',
            'Rol' => 'ROLES',
            'Pedido' => 'PEDIDOS',
        ];

        return $mapeoModelos[$modelo] ?? 'SISTEMA';
    }
}