# CU3 - Administrar Cuentas de Usuario (Modificar/Eliminar)

## Información General
- **ID del Caso de Uso**: CU3
- **Nombre**: Administrar cuentas de usuario (modificar/eliminar)
- **Prioridad**: Alta
- **Complejidad**: Alta
- **Estado**: ✅ Implementado

## Descripción
Este caso de uso permite a usuarios autorizados (administradores) gestionar las cuentas de usuario del sistema. Incluye operaciones CRUD completas: crear, listar, editar y eliminar usuarios, con validaciones de seguridad y restricciones de integridad.

## Actores
- **Actor Principal**: Administrador del Sistema
- **Actor Secundario**: Usuario (para operaciones en su propia cuenta)

## Precondiciones
1. El administrador debe estar autenticado y tener permisos adecuados
2. El sistema debe estar operativo
3. Debe existir al menos un rol definido en el sistema
4. Para edición/eliminación: el usuario objetivo debe existir

## Postcondiciones
### Éxito - Crear Usuario
1. Se crea nueva cuenta de usuario
2. Se asigna rol por defecto o especificado
3. Se envía email de bienvenida/verificación
4. Se registra la acción en logs de auditoría

### Éxito - Editar Usuario
1. Los datos del usuario quedan actualizados
2. Se registra el cambio en logs de auditoría
3. Si cambió el email, se requiere nueva verificación

### Éxito - Eliminar Usuario
1. La cuenta de usuario es eliminada (soft delete)
2. Se registra la eliminación en logs
3. Los datos relacionados se manejan según política de retención

### Fallo
1. No se realiza ningún cambio
2. Se muestra mensaje de error específico
3. Se registra el intento fallido

## Flujo Principal - Listar Usuarios
1. El administrador accede a la gestión de usuarios (`/users`)
2. El sistema verifica permisos de acceso
3. El sistema recupera lista de usuarios con paginación
4. El sistema muestra tabla con información de usuarios:
   - ID, Nombre, Email, Rol, Estado, Fecha de registro
5. El administrador puede navegar entre páginas

## Flujo Principal - Crear Usuario
1. El administrador hace clic en "Nuevo Usuario"
2. El sistema muestra formulario de creación
3. El administrador completa los campos:
   - Nombre, Teléfono, Dirección, Email, Rol, Estado, Contraseña
4. El administrador hace clic en "Crear"
5. El sistema valida los datos:
   - Email único en usuarios
   - Teléfono único en usuarios
   - Rol válido
   - Contraseña segura
6. El sistema crea la cuenta
7. Si el rol es Cliente, se crea automáticamente perfil de cliente
8. El sistema envía email de activación
9. El sistema redirige a la lista con mensaje de éxito

## Flujo Principal - Editar Usuario
1. El administrador selecciona "Editar" en un usuario
2. El sistema muestra formulario con datos actuales
3. El administrador modifica los campos necesarios
4. El administrador hace clic en "Actualizar"
5. El sistema valida los cambios
6. El sistema actualiza la información
7. El sistema redirige con mensaje de éxito

## Flujo Principal - Eliminar Usuario
1. El administrador selecciona "Eliminar" en un usuario
2. El sistema muestra confirmación
3. El administrador confirma la eliminación
4. El sistema verifica restricciones de integridad
5. El sistema elimina la cuenta (soft delete)
6. El sistema redirige con mensaje de éxito

## Flujos Alternativos

### FA1 - Usuario intenta editarse a sí mismo
1. En edición, el usuario objetivo es el mismo administrador
2. Se permiten cambios limitados (no puede cambiar su propio rol)
3. Se registra la auto-edición en logs

### FA2 - Email duplicado en edición
1. En validación de edición, el email ya existe en otro usuario
2. El sistema muestra mensaje de error
3. Retorna al formulario manteniendo cambios

### FA3 - Intento de eliminar usuario con dependencias
1. En eliminación, el usuario tiene datos relacionados
2. El sistema muestra mensaje explicativo
3. Ofrece opciones: transferir datos o cancelar

### FA4 - Creación masiva de usuarios
1. El administrador sube archivo CSV con múltiples usuarios
2. El sistema valida cada registro
3. Procesa usuarios válidos, reporta errores
4. Envía resumen por email

## Excepciones

### EX1 - Violación de integridad referencial
- **Descripción**: Intento de eliminar usuario con foreign keys
- **Tratamiento**: Mostrar dependencias, sugerir transferir o rechazar

### EX2 - Error de concurrencia
- **Descripción**: Otro administrador modifica el mismo usuario simultáneamente
- **Tratamiento**: Detectar conflicto, mostrar mensaje, refrescar datos

### EX3 - Ataque de elevación de privilegios
- **Descripción**: Intento de asignar rol superior al propio
- **Tratamiento**: Validar permisos, log de seguridad, rechazar operación

## Requisitos No Funcionales
- **Rendimiento**: Listado con paginación < 500ms
- **Seguridad**: Auditoría completa de todas las operaciones
- **Usabilidad**: Interfaz intuitiva con confirmaciones
- **Integridad**: Validaciones de negocio estrictas
- **Disponibilidad**: Funcionalidad crítica con alta disponibilidad

## Componentes Técnicos Implementados

### Controlador
- **Archivo**: `app/Http/Controllers/UserController.php`
- **Métodos**:
  - `index()`: Lista usuarios con paginación
  - `create()`: Formulario de creación
  - `store()`: Crear usuario
  - `show()`: Detalles de usuario
  - `edit()`: Formulario de edición
  - `update()`: Actualizar usuario
  - `destroy()`: Eliminar usuario

### Modelo
- **Archivo**: `app/Models/User.php`
- **Relaciones**: belongsTo con Rol, hasOne con Cliente
- **Campos**: id_rol, nombre, telefono, direccion, email, password, habilitado, timestamps

### Vistas
- **Archivos**:
  - `resources/views/users/index.blade.php` - Lista de usuarios
  - `resources/views/users/create.blade.php` - Crear usuario
  - `resources/views/users/edit.blade.php` - Editar usuario
  - `resources/views/users/show.blade.php` - Detalles de usuario
  - `resources/views/users/_form.blade.php` - Formulario reutilizable

### Rutas
```php
Route::resource('users', UserController::class);
```

### Validaciones
```php
// Creación
'id_rol' => 'required|exists:rol,id_rol',
'nombre' => 'required|string|max:255',
'telefono' => 'nullable|string|max:15|unique:usuario',
'direccion' => 'nullable|string',
'email' => 'required|string|email|max:255|unique:usuario',
'password' => 'required|string|min:8|confirmed',
'habilitado' => 'boolean'

// Edición
'id_rol' => 'required|exists:rol,id_rol',
'nombre' => 'required|string|max:255',
'telefono' => 'nullable|string|max:15|unique:usuario,telefono,'.$id.',id_usuario',
'direccion' => 'nullable|string',
'email' => 'required|string|email|max:255|unique:usuario,email,'.$id.',id_usuario',
'password' => 'nullable|string|min:8|confirmed',
'habilitado' => 'boolean'
```

### Middleware
- **Archivo**: `app/Http/Middleware/CheckRole.php`
- **Funcionalidad**: Verificación de permisos para operaciones administrativas

## Archivos Relacionados
- `app/Models/Rol.php` - Modelo de roles
- `database/factories/UserFactory.php` - Factory para pruebas
- `tests/Feature/UserManagementTest.php` - Pruebas de funcionalidad
- `resources/views/layouts/navigation.blade.php` - Navegación con menú de usuarios

## Pruebas
- **Archivo**: `tests/Feature/UserManagementTest.php`
- **Cobertura**:
  - CRUD completo de usuarios
  - Validaciones de campos
  - Restricciones de permisos
  - Integridad referencial
  - Auditoría de operaciones

## Consideraciones de Seguridad
1. **Autorización**: Verificación de roles para cada operación
2. **Auditoría**: Registro de todas las operaciones (quién, cuándo, qué)
3. **Validación**: Datos sanitizados y validados en servidor
4. **Protección CSRF**: Tokens en todos los formularios
5. **Soft Delete**: Eliminaciones no destructivas para auditoría
6. **Rate Limiting**: Control de operaciones masivas

## Políticas de Negocio
1. **Auto-eliminación**: Usuario no puede eliminarse a sí mismo
2. **Último Admin**: No se puede eliminar el último administrador
3. **Transferencia de Datos**: Al eliminar usuario, opción de reasignar recursos
4. **Historial**: Mantener logs de cambios por 7 años
5. **Notificaciones**: Email de cambios importantes

## Métricas de Éxito
- 100% de operaciones auditadas
- Tiempo de respuesta < 2 segundos para operaciones críticas
- 0% de violaciones de integridad
- Tasa de error de validación < 5%
- Satisfacción del usuario > 85%

## Historial de Cambios
- **v1.0** - Implementación básica CRUD (02/10/2025)
- **v1.1** - Agregado soft delete y auditoría (02/10/2025)
- **v1.2** - Implementado control de permisos (02/10/2025)
- **v1.3** - Agregado validaciones de negocio (02/10/2025)
- **v1.4** - Agregados campos teléfono y dirección (03/10/2025)
- **v1.5** - Implementadas validaciones de unicidad para teléfono (03/10/2025)
- **v1.6** - Agregada creación automática de cliente para rol Cliente (03/10/2025)</content>
<parameter name="filePath">c:\Users\PG\Desktop\Materias\Sistemas de Informacion 1\Grupo SC\proyecto_confeccion\modas_boom\Docs\Ciclo 1\CU3_Administrar_Usuarios.md