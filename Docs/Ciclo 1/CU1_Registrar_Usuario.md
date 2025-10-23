# CU1 - Registrar Cuenta de Usuario del Sistema

## Información General
- **ID del Caso de Uso**: CU1
- **Nombre**: Registrar cuenta de usuario del sistema
- **Prioridad**: Alta
- **Complejidad**: Media
- **Estado**: ✅ Implementado

## Descripción
Este caso de uso permite a un usuario potencial registrarse en el sistema Modas Boom creando una nueva cuenta de usuario. El proceso incluye la validación de datos, creación automática de perfil de cliente, y verificación de email.

## Actores
- **Actor Principal**: Usuario Potencial (Visitante no registrado)
- **Actores Secundarios**: Sistema de Email (para verificación)

## Precondiciones
1. El usuario debe tener acceso a internet y a un navegador web
2. El sistema debe estar operativo
3. No debe existir una cuenta con el mismo email
4. No debe existir un cliente con el mismo CI/NIT
5. No debe existir un usuario con el mismo teléfono
6. El email proporcionado debe ser válido y accesible

## Postcondiciones
### Éxito
1. Se crea una nueva cuenta de usuario en el sistema con rol "Cliente"
2. Se crea automáticamente un perfil de cliente asociado
3. Se envía un email de verificación al usuario
4. El usuario es redirigido a la página de verificación de email
5. La cuenta queda en estado "no verificado" hasta confirmar el email

### Fallo
1. No se crea ninguna cuenta ni perfil de cliente
2. Se muestra mensaje de error específico
3. El usuario permanece en la página de registro

## Flujo Principal
1. El usuario accede a la página de registro (`/register`)
2. El sistema muestra el formulario de registro con los campos:
   - Nombre
   - Apellido
   - CI/NIT
   - Teléfono
   - Dirección
   - Email
   - Contraseña
   - Confirmación de contraseña
3. El usuario completa todos los campos requeridos
4. El usuario hace clic en "Registrarse"
5. El sistema valida los datos:
   - Nombre: requerido, string, máximo 255 caracteres
   - Apellido: requerido, string, máximo 255 caracteres
   - CI/NIT: requerido, string, máximo 20 caracteres, único en clientes
   - Teléfono: opcional, string, máximo 15 caracteres, único en usuarios y clientes
   - Dirección: opcional, string
   - Email: requerido, formato válido, único en usuarios
   - Contraseña: requerido, mínimo 8 caracteres, coincide con confirmación
6. El sistema crea la cuenta de usuario con:
   - Estado: no verificado
   - Rol: Cliente (id_rol = 3)
   - Timestamp de creación
7. El sistema crea automáticamente un perfil de cliente con los mismos datos
8. El sistema envía email de verificación
9. El sistema redirige al usuario a la página de verificación
10. El usuario confirma su email haciendo clic en el enlace
11. La cuenta queda verificada y el usuario puede iniciar sesión

## Flujos Alternativos

### FA1 - Email ya registrado
1. En el paso 5 del flujo principal, el email ya existe en usuarios
2. El sistema muestra mensaje: "El email ya está registrado"
3. El sistema mantiene los datos del formulario (excepto contraseña)
4. Retorna al paso 3

### FA2 - CI/NIT ya registrado
1. En el paso 5 del flujo principal, el CI/NIT ya existe en clientes
2. El sistema muestra mensaje: "El CI/NIT ya está registrado"
3. El sistema mantiene los datos del formulario (excepto contraseña)
4. Retorna al paso 3

### FA3 - Teléfono ya registrado
1. En el paso 5 del flujo principal, el teléfono ya existe en usuarios o clientes
2. El sistema muestra mensaje: "El teléfono ya está registrado"
3. El sistema mantiene los datos del formulario (excepto contraseña)
4. Retorna al paso 3

## Excepciones

### EX1 - Error de base de datos
- **Descripción**: Error al guardar en la base de datos
- **Tratamiento**: Rollback de transacción, log del error, mensaje genérico al usuario

### EX2 - Email inválido
- **Descripción**: El email no tiene formato válido
- **Tratamiento**: Validación del lado cliente y servidor, mensaje específico

### EX3 - Ataque de registro masivo
- **Descripción**: Intentos de registro automatizados
- **Tratamiento**: Rate limiting, CAPTCHA (si implementado)

## Requisitos No Funcionales
- **Rendimiento**: Registro debe completarse en menos de 3 segundos
- **Seguridad**: Contraseñas hasheadas con bcrypt
- **Usabilidad**: Formulario claro con validación en tiempo real
- **Accesibilidad**: Compatible con lectores de pantalla
- **Disponibilidad**: Funcional 99.9% del tiempo

## Componentes Técnicos Implementados

### Controlador
- **Archivo**: `app/Http/Controllers/Auth/RegisteredUserController.php`
- **Métodos**:
  - `create()`: Muestra formulario de registro
  - `store()`: Procesa el registro

### Modelo
- **Archivo**: `app/Models/User.php`
- **Campos**: nombre, email, password, email_verified_at, timestamps
- **Relaciones**: belongsTo con Rol

### Vistas
- **Archivo**: `resources/views/auth/register.blade.php`
- **Componentes**: Formulario con validación, mensajes de error

### Rutas
```php
Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store']);
```

### Validaciones
```php
$request->validate([
    'nombre' => ['required', 'string', 'max:255'],
    'apellido' => ['required', 'string', 'max:255'],
    'ci_nit' => ['required', 'string', 'max:20', 'unique:clientes'],
    'telefono' => ['nullable', 'string', 'max:15', 'unique:usuario', 'unique:clientes'],
    'direccion' => ['nullable', 'string'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
]);
```

### Creación automática de cliente
Después de crear el usuario, el sistema automáticamente crea un registro en la tabla `clientes` con los mismos datos personales.

### Modelo
- **Archivo**: `app/Models/User.php`
- **Campos**: id_rol, nombre, telefono, direccion, email, password, habilitado, timestamps
- **Relaciones**: belongsTo con Rol, hasOne con Cliente

### Modelo Cliente
- **Archivo**: `app/Models/Cliente.php`
- **Campos**: id_usuario, nombre, apellido, ci_nit, telefono, email, direccion, timestamps
- **Relaciones**: belongsTo con User

## Pruebas
- **Archivo**: `tests/Feature/Auth/RegistrationTest.php`
- **Cobertura**:
  - Registro exitoso
  - Validación de campos requeridos
  - Email duplicado
  - Contraseña débil
  - Verificación de email

## Consideraciones de Seguridad
1. **Hashing de contraseñas**: Implementado con bcrypt
2. **Validación de email**: Verificación obligatoria antes del acceso
3. **Protección CSRF**: Token incluido en formularios
4. **Rate limiting**: Control de intentos de registro
5. **Sanitización**: Datos limpiados antes del procesamiento

## Métricas de Éxito
- Tasa de conversión de registro > 70%
- Tiempo promedio de registro < 2 minutos
- Tasa de verificación de email > 80%
- Menos de 1% de errores de validación por sesión

## Historial de Cambios
- **v1.0** - Implementación inicial (02/10/2025)
- **v1.1** - Agregado rate limiting (02/10/2025)
- **v1.2** - Mejora de UX en validaciones (02/10/2025)
- **v1.3** - Agregado campos adicionales (nombre, apellido, CI/NIT, teléfono, dirección) (03/10/2025)
- **v1.4** - Implementado creación automática de perfil cliente (03/10/2025)
- **v1.5** - Agregadas validaciones de unicidad para teléfono y CI/NIT (03/10/2025)</content>
<parameter name="filePath">c:\Users\PG\Desktop\Materias\Sistemas de Informacion 1\Grupo SC\proyecto_confeccion\modas_boom\Docs\Ciclo 1\CU1_Registrar_Usuario.md