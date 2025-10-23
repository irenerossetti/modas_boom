# CU6 - Cerrar Sesión del Sistema

## Información General
- **ID del Caso de Uso**: CU6
- **Nombre**: Cerrar sesión del sistema
- **Prioridad**: Alta
- **Complejidad**: Media
- **Estado**: ✅ Implementado

## Descripción
Este caso de uso permite a usuarios autenticados cerrar su sesión de manera segura en el sistema Modas Boom. Incluye la invalidación completa de la sesión, eliminación de tokens de autenticación, regeneración de identificadores de sesión y limpieza de datos temporales para prevenir ataques de session hijacking.

## Actores
- **Actor Principal**: Usuario Autenticado
- **Actores Secundarios**:
  - Sistema de Sesiones
  - Sistema de Logs de Auditoría

## Precondiciones
1. El usuario debe estar actualmente autenticado en el sistema
2. Debe existir una sesión activa válida
3. El sistema debe estar operativo
4. Los logs de auditoría deben estar habilitados

## Postcondiciones
### Éxito
1. La sesión del usuario es completamente invalidada
2. Todos los tokens de autenticación son eliminados
3. Se crea una nueva sesión vacía para prevenir fixation attacks
4. El usuario es redirigido a la página de inicio
5. Se registra el logout en logs de auditoría
6. Los datos temporales de sesión son limpiados

### Fallo
1. La sesión permanece activa (en casos excepcionales)
2. Se muestra mensaje de error
3. Se registra el intento fallido de logout

## Flujo Principal
1. Usuario autenticado hace clic en "Cerrar Sesión" en la interfaz
2. El sistema recibe la solicitud POST a `/logout`
3. El sistema verifica que el usuario esté autenticado
4. El sistema valida el token CSRF de la solicitud
5. El sistema registra el evento de logout en auditoría:
   - Usuario, timestamp, IP, user agent
6. El sistema invalida la sesión actual:
   - Elimina todos los datos de sesión
   - Invalida el session ID
7. El sistema regenera un nuevo session ID vacío
8. El sistema elimina cookies de autenticación:
   - Cookie de sesión
   - Cookie "remember me" (si existe)
9. El sistema limpia datos temporales asociados al usuario
10. El sistema redirige al usuario a la página de inicio
11. El sistema muestra mensaje de confirmación (opcional)

## Flujos Alternativos

### FA1 - Logout desde Múltiples Dispositivos
1. Usuario tiene sesiones activas en múltiples dispositivos
2. Selecciona opción "Cerrar sesión en todos los dispositivos"
3. El sistema invalida todas las sesiones del usuario
4. Se registra logout masivo en auditoría
5. Se notifican otros dispositivos (opcional)

### FA2 - Logout Automático por Inactividad
1. El sistema detecta inactividad prolongada (configurable)
2. El sistema inicia proceso de logout automático
3. Se muestra advertencia al usuario (si está activo)
4. Si no hay respuesta, se ejecuta logout automático
5. Se registra como logout por timeout

### FA3 - Logout Forzado por Administrador
1. Administrador fuerza logout de un usuario específico
2. El sistema invalida todas las sesiones del usuario
3. El usuario es redirigido a login con mensaje explicativo
4. Se registra logout forzado en auditoría

### FA4 - Logout en Caso de Emergencia
1. Se detecta actividad sospechosa en la cuenta
2. El sistema activa protocolo de seguridad
3. Se fuerza logout inmediato de todas las sesiones
4. Se bloquea temporalmente la cuenta
5. Se envía notificación de seguridad al usuario

## Excepciones

### EX1 - Error de Sesión
- **Descripción**: Problema con el sistema de sesiones
- **Tratamiento**: Logging del error, mensaje genérico al usuario

### EX2 - Token CSRF Inválido
- **Descripción**: Token de protección contra CSRF no válido
- **Tratamiento**: Rechazar solicitud, log de posible ataque CSRF

### EX3 - Usuario No Autenticado
- **Descripción**: Intento de logout sin estar autenticado
- **Tratamiento**: Redirigir a login, log de comportamiento inusual

## Requisitos No Funcionales
- **Seguridad**: Invalidación completa de sesión en < 1 segundo
- **Rendimiento**: Logout debe completarse en < 500ms
- **Disponibilidad**: Funcionalidad crítica con alta disponibilidad
- **Auditabilidad**: 100% de eventos de logout registrados
- **Usabilidad**: Proceso transparente al usuario

## Componentes Técnicos Implementados

### Controlador de Logout
- **Archivo**: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- **Método**: `destroy(Request $request)`
- **Funcionalidad**:
  - Invalidación de sesión con `Auth::guard('web')->logout()`
  - Invalidate session con `$request->session()->invalidate()`
  - Regeneración de token con `$request->session()->regenerateToken()`
  - Redirección segura a `/`

### Middleware de Protección
- **Archivo**: Rutas protegidas en `routes/web.php`
- **Funcionalidad**: Verificación de autenticación antes de logout

### Sistema de Sesiones
- **Configuración**: `config/session.php`
  - Driver de sesiones (database/file/redis)
  - Tiempo de expiración
  - Configuración de cookies seguras

### Auditoría de Logout
- **Archivo**: `app/Services/AuditLogger.php`
- **Funcionalidad**:
  - Registro de eventos de logout
  - Categorización por tipo (manual, automático, forzado)
  - Asociación con usuario e IP

## Vistas y Navegación
- **Archivo**: `resources/views/layouts/navigation.blade.php`
- **Componente**: Formulario de logout con método POST y CSRF
```blade
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="dropdown-link">
        {{ __('Cerrar Sesión') }}
    </button>
</form>
```

## Rutas
```php
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

## Archivos Relacionados
- `app/Http/Middleware/Authenticate.php` - Middleware de autenticación
- `config/auth.php` - Configuración de guards y providers
- `database/migrations/*_create_sessions_table.php` - Tabla de sesiones
- `tests/Feature/LogoutTest.php` - Pruebas de funcionalidad

## Pruebas
- **Archivo**: `tests/Feature/LogoutTest.php`
- **Cobertura**:
  - Logout exitoso con invalidación de sesión
  - Verificación de redirección correcta
  - Comprobación de eliminación de cookies
  - Auditoría de eventos de logout
  - Manejo de errores

## Consideraciones de Seguridad
1. **Invalidación Completa**: Eliminación total de datos de sesión
2. **Prevención de Session Fixation**: Regeneración de session ID
3. **Protección CSRF**: Token requerido en todas las solicitudes
4. **Auditoría Completa**: Registro de todos los eventos de logout
5. **Limpieza de Cookies**: Eliminación de tokens de remember me
6. **Timeouts Automáticos**: Logout por inactividad prolongada

## Políticas de Sesión
1. **Expiración por Inactividad**: 2 horas sin actividad
2. **Expiración Absoluta**: 24 horas máximo por sesión
3. **Máximo de Sesiones Concurrentes**: 5 por usuario
4. **Recordar Sesión**: Hasta 30 días con token seguro
5. **Invalidación Masiva**: Opción de cerrar todas las sesiones

## Tipos de Logout
1. **Manual**: Usuario hace clic en "Cerrar Sesión"
2. **Automático**: Por inactividad o expiración
3. **Forzado**: Por administrador o detección de seguridad
4. **Masivo**: Cerrar todas las sesiones del usuario

## Métricas de Éxito
- **Tasa de Logout Exitoso**: > 99.9%
- **Tiempo de Respuesta**: < 500ms
- **Sesiones Huérfanas**: < 0.1%
- **Eventos Auditados**: 100%
- **Satisfacción del Usuario**: > 95%

## Historial de Cambios
- **v1.0** - Implementación básica de logout (02/10/2025)
- **v1.1** - Agregado invalidación completa de sesión (02/10/2025)
- **v1.2** - Implementado sistema de auditoría (02/10/2025)
- **v1.3** - Agregado logout masivo y automático (02/10/2025)</content>
<parameter name="filePath">c:\Users\PG\Desktop\Materias\Sistemas de Informacion 1\Grupo SC\proyecto_confeccion\modas_boom\Docs\Ciclo 1\CU6_Cerrar_Sesion.md