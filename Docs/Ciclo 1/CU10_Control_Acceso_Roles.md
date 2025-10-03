# CU10 - Control de Acceso Basado en Roles

## Información General
- **ID del Caso de Uso**: CU10
- **Nombre**: Control de acceso basado en roles
- **Prioridad**: Crítica
- **Complejidad**: Alta
- **Estado**: ✅ Implementado

## Descripción
Este caso de uso implementa un sistema completo de control de acceso basado en roles (RBAC) para el sistema Modas Boom. Define tres roles principales (Administrador, Empleado, Cliente) con diferentes niveles de permisos y restricciones de acceso a funcionalidades del sistema.

## Actores
- **Actor Principal**: Sistema de Autenticación
- **Actores Secundarios**:
  - Usuario Administrador
  - Usuario Empleado
  - Usuario Cliente

## Precondiciones
1. El sistema debe tener definida la estructura de roles
2. Los usuarios deben estar correctamente asignados a roles
3. El sistema de middleware debe estar configurado
4. Las rutas deben tener protección por middleware

## Postcondiciones
### Éxito
1. Cada usuario tiene acceso únicamente a las funcionalidades de su rol
2. Las operaciones no autorizadas son bloqueadas
3. Se registra el acceso no autorizado en logs
4. El usuario es redirigido apropiadamente

### Fallo
1. El sistema mantiene la integridad de acceso
2. Se registra el intento de acceso no autorizado
3. El usuario recibe mensaje de error apropiado

## Roles del Sistema

### Administrador (id_rol = 1)
**Permisos Completos:**
- Acceso a dashboard principal (`/dashboard`)
- Gestión completa de usuarios (CRUD)
- Gestión completa de roles (CRUD)
- Gestión completa de clientes (CRUD)
- Todas las funcionalidades del sistema

### Empleado (id_rol = 2)
**Permisos Limitados:**
- Acceso a dashboard de empleado (`/empleado-dashboard`)
- Vista de solo lectura de clientes
- Acceso a perfil personal
- No puede crear, editar o eliminar clientes
- No puede gestionar usuarios o roles

### Cliente (id_rol = 3)
**Permisos Mínimos:**
- Acceso a página de inicio (`/`)
- Acceso a perfil personal
- No tiene acceso al panel administrativo

## Flujo Principal - Verificación de Acceso
1. Usuario solicita acceso a una funcionalidad
2. Sistema verifica si usuario está autenticado
3. Sistema verifica si usuario está habilitado
4. Sistema verifica rol del usuario
5. Sistema compara rol con permisos requeridos para la funcionalidad
6. Si tiene permisos: permite acceso
7. Si no tiene permisos: bloquea acceso y redirige

## Flujo Principal - Redirección por Rol (Login)
1. Usuario inicia sesión exitosamente
2. Sistema identifica el rol del usuario
3. Sistema redirige según rol:
   - Administrador → `/dashboard`
   - Empleado → `/empleado-dashboard`
   - Cliente → `/`
4. Usuario accede a su interfaz correspondiente

## Flujo Principal - Navegación Condicional
1. Usuario accede al sistema
2. Sistema carga la interfaz según rol
3. **Administrador**: Ve menú completo (Dashboard, Usuarios, Roles, Clientes)
4. **Empleado**: Ve menú limitado (Dashboard Empleado, Clientes)
5. **Cliente**: No ve menú administrativo
6. Sistema oculta opciones no disponibles para el rol

## Flujos Alternativos

### FA1 - Intento de Acceso Directo No Autorizado
1. Usuario intenta acceder directamente a URL restringida
2. Middleware intercepta la petición
3. Sistema verifica permisos
4. Si no autorizado: redirige a página apropiada
5. Se registra el intento en logs de seguridad

### FA2 - Cambio de Rol de Usuario
1. Administrador cambia rol de un usuario
2. Sistema verifica implicaciones del cambio
3. Si cambia a Cliente: crea perfil de cliente automáticamente
4. Si cambia de Cliente a otro: elimina perfil de cliente
5. Usuario afectado debe reiniciar sesión para aplicar cambios

### FA3 - Usuario con Múltiples Roles
1. Sistema detecta usuario con rol no estándar
2. Aplica permisos del rol más restrictivo
3. Registra anomalía para revisión administrativa
4. Usuario puede continuar con permisos limitados

## Excepciones

### EX1 - Error en Middleware
- **Descripción**: Fallo en la verificación de permisos
- **Tratamiento**: Denegar acceso por seguridad, log del error

### EX2 - Rol No Definido
- **Descripción**: Usuario con rol inexistente
- **Tratamiento**: Asignar permisos mínimos, alertar administrador

### EX3 - Conflicto de Sesión
- **Descripción**: Usuario con sesión activa cambia de rol
- **Tratamiento**: Invalidar sesión actual, requerir re-login

## Requisitos No Funcionales
- **Seguridad**: 100% de operaciones autorizadas verificadas
- **Rendimiento**: Verificación de permisos < 100ms
- **Disponibilidad**: Control de acceso siempre activo
- **Auditabilidad**: Registro completo de accesos y denegaciones
- **Mantenibilidad**: Fácil modificación de permisos por rol

## Componentes Técnicos Implementados

### Middleware de Control de Acceso
- **Archivo**: `app/Http/Middleware/CheckAdminRole.php`
- **Funcionalidad**: Verifica rol de administrador para rutas protegidas

### Controlador de Autenticación
- **Archivo**: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- **Método**: `store()` con lógica de redirección por rol

### Navegación Condicional
- **Archivo**: `resources/views/layouts/app.blade.php`
- **Funcionalidad**: Menú lateral que se adapta al rol del usuario

### Vistas de Solo Lectura
- **Archivo**: `resources/views/clientes/index.blade.php`
- **Funcionalidad**: Oculta botones de acción para empleados

### Rutas Protegidas
```php
// Acceso general (todos los roles autenticados)
Route::middleware(['auth', 'user.enabled'])->group(function () {
    Route::get('clientes', [ClienteController::class, 'index']);
    // Otras rutas comunes
});

// Acceso administrativo (solo administradores)
Route::middleware(['auth', 'user.enabled', 'admin.role'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('roles', RolController::class);
    Route::resource('clientes', ClienteController::class)->except(['index']);
});
```

### Modelo de Usuario
- **Archivo**: `app/Models/User.php`
- **Campo**: `id_rol` para determinar permisos
- **Relación**: `belongsTo` con modelo Rol

## Archivos Relacionados
- `app/Models/Rol.php` - Definición de roles del sistema
- `database/seeders/RolSeeder.php` - Datos iniciales de roles
- `bootstrap/app.php` - Registro del middleware
- `routes/web.php` - Definición de rutas protegidas

## Pruebas del Sistema
- **Archivo**: `tests/Feature/RoleAccessTest.php`
- **Cobertura**:
  - Acceso autorizado por rol
  - Denegación de acceso no autorizado
  - Redirección correcta por rol
  - Navegación condicional
  - Middleware funcionando correctamente

## Consideraciones de Seguridad
1. **Principio de Menor Privilegio**: Usuarios solo tienen permisos necesarios
2. **Separación de Funciones**: Roles claramente diferenciados
3. **Auditoría de Acceso**: Registro de todas las operaciones
4. **Falla Segura**: Denegar acceso en caso de duda
5. **Sesión Segura**: Invalidación al cambiar permisos

## Políticas de Control de Acceso
1. **Asignación de Roles**: Solo administradores pueden cambiar roles
2. **Herencia de Permisos**: No implementada (roles independientes)
3. **Revocación Inmediata**: Cambios de rol aplican inmediatamente
4. **Mínimo Administrador**: Siempre debe existir al menos un administrador
5. **Auto-restricción**: Usuario no puede quitarse permisos a sí mismo

## Métricas de Éxito
- **Tasa de Acceso Autorizado**: > 99.9%
- **Tasa de Detección de Intrusiones**: > 95%
- **Tiempo de Verificación**: < 50ms promedio
- **Satisfacción del Usuario**: > 90% (interfaz clara)
- **Cumplimiento de Políticas**: 100%

## Historial de Cambios
- **v1.0** - Implementación inicial del sistema RBAC (03/10/2025)
- **v1.1** - Middleware de verificación de roles (03/10/2025)
- **v1.2** - Redirección automática por rol en login (03/10/2025)
- **v1.3** - Navegación condicional en interfaz (03/10/2025)
- **v1.4** - Protección de rutas administrativas (03/10/2025)
- **v1.5** - Creación automática de perfiles cliente (03/10/2025)</content>
<parameter name="filePath">c:\Users\PG\Desktop\Materias\Sistemas de Informacion 1\Grupo SC\proyecto_confeccion\modas_boom\Docs\Ciclo 1\CU10_Control_Acceso_Roles.md