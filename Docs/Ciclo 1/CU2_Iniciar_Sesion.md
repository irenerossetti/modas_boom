# CU2 - Iniciar Sesión con Credenciales

## Información General
- **ID del Caso de Uso**: CU2
- **Nombre**: Iniciar sesión con credenciales
- **Prioridad**: Crítica
- **Complejidad**: Media
- **Estado**: ✅ Implementado

## Descripción
Este caso de uso permite a un usuario registrado autenticarse en el sistema Modas Boom mediante sus credenciales (email y contraseña). Incluye validación de credenciales, verificación de estado de cuenta y regeneración de sesión por seguridad.

## Actores
- **Actor Principal**: Usuario Registrado
- **Actores Secundarios**: Sistema de Autenticación, Base de Datos

## Precondiciones
1. El usuario debe tener una cuenta registrada y verificada
2. La cuenta debe estar habilitada (no bloqueada)
3. El usuario debe tener acceso a internet y navegador web
4. El sistema debe estar operativo

## Postcondiciones
### Éxito
1. El usuario queda autenticado en el sistema
2. Se crea una nueva sesión segura
3. El usuario es redirigido según su rol:
   - **Administrador**: Dashboard principal (`/dashboard`)
   - **Empleado**: Dashboard de empleado (`/empleado-dashboard`)
   - **Cliente**: Página de inicio (`/`)
4. Se registra el login exitoso en logs de auditoría

### Fallo
1. El usuario permanece no autenticado
2. Se muestra mensaje de error específico
3. Se registra el intento fallido
4. Posible bloqueo temporal por intentos excesivos

## Flujo Principal
1. El usuario accede a la página de login (`/login`)
2. El sistema muestra el formulario de login con campos:
   - Email
   - Contraseña
   - Checkbox "Recordarme" (opcional)
3. El usuario ingresa sus credenciales
4. El usuario hace clic en "Iniciar Sesión"
5. El sistema valida el formato de los datos:
   - Email: requerido, formato válido
   - Contraseña: requerida
6. El sistema verifica las credenciales contra la base de datos
7. El sistema verifica que la cuenta esté habilitada
8. El sistema verifica que el email esté confirmado
9. El sistema crea una nueva sesión segura
10. El sistema regenera el token CSRF
11. El sistema determina el rol del usuario y redirige apropiadamente:
    - Si rol = Administrador (id_rol = 1): redirige a `/dashboard`
    - Si rol = Empleado (id_rol = 2): redirige a `/empleado-dashboard`
    - Si rol = Cliente (id_rol = 3): redirige a `/`

## Flujos Alternativos

### FA1 - Credenciales incorrectas
1. En el paso 6, las credenciales no coinciden
2. El sistema registra el intento fallido
3. El sistema muestra mensaje: "Credenciales incorrectas"
4. Si hay muchos intentos fallidos, se activa throttling
5. Retorna al paso 3

### FA2 - Cuenta no verificada
1. En el paso 8, el email no está verificado
2. El sistema muestra mensaje: "Debe verificar su email antes de iniciar sesión"
3. El sistema ofrece opción de reenviar email de verificación
4. Retorna al paso 3

### FA3 - Cuenta bloqueada
1. En el paso 7, la cuenta está deshabilitada
2. El sistema muestra mensaje: "Cuenta bloqueada. Contacte al administrador"
3. Se registra el intento de acceso a cuenta bloqueada
4. El flujo termina

### FA4 - Opción "Recordarme" activada
1. En el paso 9, el usuario marcó "Recordarme"
2. El sistema crea cookie de "remember me" con token seguro
3. La sesión se mantiene por período extendido (configurable)

## Excepciones

### EX1 - Error de base de datos
- **Descripción**: Problema de conexión o consulta
- **Tratamiento**: Log del error, mensaje genérico "Error del sistema"

### EX2 - Sesión corrupta
- **Descripción**: Problema con la gestión de sesiones
- **Tratamiento**: Invalidar sesión actual, crear nueva

### EX3 - Ataque de fuerza bruta
- **Descripción**: Múltiples intentos de login automatizados
- **Tratamiento**: Bloqueo temporal, logging de IP sospechosa

## Requisitos No Funcionales
- **Rendimiento**: Login debe completarse en menos de 2 segundos
- **Seguridad**: Hashing bcrypt para contraseñas, HTTPS obligatorio
- **Disponibilidad**: 99.9% uptime para funcionalidad crítica
- **Usabilidad**: Mensajes de error claros y específicos
- **Auditabilidad**: Registro de todos los intentos de login

## Componentes Técnicos Implementados

### Controlador
- **Archivo**: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- **Métodos**:
  - `create()`: Muestra formulario de login
  - `store()`: Procesa la autenticación y redirección basada en roles

### Lógica de Redirección por Roles
```php
$user = Auth::user();
if ($user->id_rol == 1) { // Admin
    return redirect(route('dashboard', absolute: false));
} elseif ($user->id_rol == 2) { // Empleado
    return redirect('/empleado-dashboard');
} else { // Cliente
    return redirect('/');
}
```

### Request Class
- **Archivo**: `app/Http/Requests/Auth/LoginRequest.php`
- **Validaciones**:
  - Email: requerido, formato válido
  - Password: requerida
  - Rate limiting automático

### Middleware
- **Archivo**: `app/Http/Middleware/LoginAttemptThrottle.php`
- **Funcionalidad**: Control de intentos fallidos por IP/usuario
- **Archivo**: `app/Http/Middleware/UserEnabled.php`
- **Funcionalidad**: Verificación de estado de cuenta

### Vistas
- **Archivo**: `resources/views/auth/login.blade.php`
- **Componentes**: Formulario con validación, mensajes de error, enlace "Olvidé contraseña"

### Rutas
```php
Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('login.throttle');
```

### Configuración
- **Archivo**: `config/auth.php`
- **Guards**: Configuración de guards web y API
- **Providers**: Configuración de proveedores de usuarios

## Archivos Relacionados
- `app/Models/User.php` - Modelo de usuario con métodos de autenticación
- `database/migrations/*_create_users_table.php` - Estructura de tabla users
- `routes/auth.php` - Definición de rutas de autenticación
- `resources/views/layouts/app.blade.php` - Layout con navegación de usuario

## Pruebas
- **Archivo**: `tests/Feature/Auth/AuthenticationTest.php`
- **Cobertura**:
  - Login exitoso
  - Credenciales inválidas
  - Cuenta no verificada
  - Cuenta bloqueada
  - Rate limiting
  - Remember me functionality

## Consideraciones de Seguridad
1. **Protección contra timing attacks**: Comparación segura de hashes
2. **Rate limiting**: Máximo 5 intentos por minuto por IP
3. **Session security**: Regeneración de ID en login
4. **CSRF protection**: Tokens incluidos en formularios
5. **Secure cookies**: HttpOnly, Secure, SameSite flags
6. **Audit logging**: Registro de todos los eventos de autenticación

## Métricas de Éxito
- Tasa de login exitoso > 95%
- Tiempo promedio de login < 1.5 segundos
- Menos de 0.1% de cuentas comprometidas
- 100% de sesiones seguras (HTTPS)
- Tasa de detección de ataques > 99%

## Historial de Cambios
- **v1.0** - Implementación básica de login (02/10/2025)
- **v1.1** - Agregado rate limiting (02/10/2025)
- **v1.2** - Implementado middleware de verificación de usuario (02/10/2025)
- **v1.3** - Agregado logging de auditoría (02/10/2025)
- **v1.4** - Implementado redirección basada en roles después del login (03/10/2025)</content>
<parameter name="filePath">c:\Users\PG\Desktop\Materias\Sistemas de Informacion 1\Grupo SC\proyecto_confeccion\modas_boom\Docs\Ciclo 1\CU2_Iniciar_Sesion.md