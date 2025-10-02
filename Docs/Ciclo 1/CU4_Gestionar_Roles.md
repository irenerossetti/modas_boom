# CU4 - Gestionar Roles y Permisos de Acceso

## Información General
- **ID del Caso de Uso**: CU4
- **Nombre**: Gestionar roles y permisos de acceso
- **Prioridad**: Alta
- **Complejidad**: Alta
- **Estado**: ✅ Implementado

## Descripción
Este caso de uso permite a administradores del sistema gestionar los roles y permisos de acceso. Incluye la creación, modificación y eliminación de roles, así como la asignación de usuarios a roles específicos. El sistema implementa un modelo de autorización basado en roles (RBAC) para controlar el acceso a diferentes funcionalidades.

## Actores
- **Actor Principal**: Administrador del Sistema
- **Actor Secundario**: Sistema de Autorización

## Precondiciones
1. El administrador debe estar autenticado
2. El administrador debe tener rol de super-administrador
3. El sistema debe tener al menos un rol definido
4. Debe existir tabla de roles en la base de datos

## Postcondiciones
### Éxito - Crear Rol
1. Se crea nuevo rol en el sistema
2. El rol queda disponible para asignación a usuarios
3. Se registra la creación en logs de auditoría

### Éxito - Editar Rol
1. Los datos del rol quedan actualizados
2. Los usuarios con ese rol mantienen sus permisos actualizados
3. Se registra el cambio en auditoría

### Éxito - Eliminar Rol
1. El rol es eliminado si no tiene usuarios asignados
2. Los usuarios pierden el rol (deben reasignarse)
3. Se registra la eliminación

### Fallo
1. No se realiza ningún cambio
2. Se muestra mensaje de error específico
3. Se registra el intento fallido

## Flujo Principal - Listar Roles
1. El administrador accede a gestión de roles (`/roles`)
2. El sistema verifica permisos de super-administrador
3. El sistema recupera lista de roles con estadísticas:
   - Nombre, descripción, estado, cantidad de usuarios
4. El sistema muestra tabla paginada con acciones disponibles
5. El administrador puede filtrar/buscar roles

## Flujo Principal - Crear Rol
1. El administrador hace clic en "Nuevo Rol"
2. El sistema muestra formulario de creación
3. El administrador ingresa:
   - Nombre del rol (único)
   - Descripción detallada
   - Estado (habilitado/deshabilitado)
4. El administrador hace clic en "Crear"
5. El sistema valida unicidad del nombre
6. El sistema crea el rol con ID automático
7. El sistema redirige con mensaje de éxito

## Flujo Principal - Editar Rol
1. El administrador selecciona "Editar" en un rol
2. El sistema carga datos actuales del rol
3. El administrador modifica campos necesarios
4. El administrador hace clic en "Actualizar"
5. El sistema valida cambios (unicidad, etc.)
6. El sistema actualiza el rol
7. El sistema redirige con confirmación

## Flujo Principal - Ver Detalles de Rol
1. El administrador selecciona "Ver" en un rol
2. El sistema muestra información completa del rol
3. Se incluye lista de usuarios asignados al rol
4. El administrador puede navegar a edición o eliminación

## Flujo Principal - Eliminar Rol
1. El administrador selecciona "Eliminar" en un rol
2. El sistema verifica si tiene usuarios asignados
3. Si no tiene usuarios: muestra confirmación
4. Si tiene usuarios: muestra advertencia y lista de afectados
5. El administrador confirma la eliminación
6. El sistema elimina el rol
7. El sistema redirige con mensaje de éxito

## Flujos Alternativos

### FA1 - Intento de eliminar rol con usuarios
1. En paso 2 de eliminación, el rol tiene usuarios asignados
2. El sistema muestra mensaje: "No se puede eliminar rol con usuarios asignados"
3. El sistema ofrece opciones:
   - Reasignar usuarios a otro rol
   - Cancelar operación
4. Si se reasigna: continúa con eliminación
5. Si se cancela: retorna a lista

### FA2 - Rol por defecto del sistema
1. El administrador intenta eliminar un rol crítico (admin, user)
2. El sistema muestra mensaje: "Este rol es crítico para el sistema"
3. La operación es rechazada
4. Se registra el intento en logs de seguridad

### FA3 - Cambio masivo de roles
1. El administrador selecciona múltiples usuarios
2. Elige cambiar rol para todos
3. El sistema valida permisos para el nuevo rol
4. Aplica cambios en lote
5. Envía notificaciones a usuarios afectados

### FA4 - Importación de roles
1. El administrador sube archivo con definición de roles
2. El sistema valida formato y datos
3. Crea roles válidos, reporta errores
4. Actualiza permisos relacionados

## Excepciones

### EX1 - Violación de unicidad
- **Descripción**: Nombre de rol duplicado
- **Tratamiento**: Validación en cliente y servidor, mensaje específico

### EX2 - Error de concurrencia
- **Descripción**: Múltiples administradores editando simultáneamente
- **Tratamiento**: Detección de conflictos, refresco de datos

### EX3 - Ataque de escalada de privilegios
- **Descripción**: Intento de crear rol con permisos superiores
- **Tratamiento**: Validación de permisos del creador, logging de seguridad

## Requisitos No Funcionales
- **Rendimiento**: Operaciones críticas < 1 segundo
- **Seguridad**: Auditoría completa de cambios de roles
- **Usabilidad**: Interfaz clara con validaciones en tiempo real
- **Integridad**: Validaciones estrictas de negocio
- **Escalabilidad**: Soporte para cientos de roles

## Componentes Técnicos Implementados

### Controlador
- **Archivo**: `app/Http/Controllers/RolController.php`
- **Métodos**:
  - `index()`: Lista roles con paginación
  - `create()`: Formulario de creación
  - `store()`: Crear rol con validaciones
  - `show()`: Detalles del rol y usuarios asignados
  - `edit()`: Formulario de edición
  - `update()`: Actualizar rol
  - `destroy()`: Eliminar rol con verificaciones

### Modelo
- **Archivo**: `app/Models/Rol.php`
- **Relaciones**: hasMany con User
- **Campos**: nombre, descripcion, habilitado, timestamps

### Vistas
- **Archivos**:
  - `resources/views/roles/index.blade.php` - Lista de roles
  - `resources/views/roles/create.blade.php` - Crear rol
  - `resources/views/roles/edit.blade.php` - Editar rol
  - `resources/views/roles/show.blade.php` - Detalles de rol
  - `resources/views/roles/_form.blade.php` - Formulario reutilizable

### Rutas
```php
Route::resource('roles', RolController::class);
```

### Validaciones
```php
// Creación
'nombre' => 'required|string|max:255|unique:rol',
'descripcion' => 'nullable|string|max:500',
'habilitado' => 'boolean'

// Edición
'nombre' => 'required|string|max:255|unique:rol,nombre,'.$rol->id_rol.',id_rol',
'descripcion' => 'nullable|string|max:500',
'habilitado' => 'boolean'
```

### Middleware de Autorización
- **Archivo**: `app/Http/Middleware/CheckSuperAdmin.php`
- **Funcionalidad**: Verificación de permisos para gestión de roles

## Archivos Relacionados
- `app/Models/User.php` - Relación belongsTo con Rol
- `database/migrations/*_create_rol_table.php` - Estructura de tabla
- `database/factories/RolFactory.php` - Factory para pruebas
- `tests/Feature/RoleManagementTest.php` - Suite de pruebas
- `config/roles.php` - Configuración de roles del sistema

## Pruebas
- **Archivo**: `tests/Feature/RoleManagementTest.php`
- **Cobertura**:
  - CRUD completo de roles
  - Validaciones de unicidad
  - Restricciones de eliminación
  - Autorización de operaciones
  - Integridad referencial

## Consideraciones de Seguridad
1. **Principio de Menor Privilegio**: Solo super-administradores pueden gestionar roles
2. **Auditoría Completa**: Registro de todos los cambios de roles
3. **Validación Estricta**: No permitir creación de roles con permisos excesivos
4. **Separación de Deberes**: Un usuario no puede modificar su propio rol
5. **Logging de Seguridad**: Registro de intentos de escalada de privilegios

## Políticas de Roles
1. **Roles Críticos**: Admin y User no pueden eliminarse
2. **Jerarquía de Roles**: Validación de niveles de acceso
3. **Transición de Roles**: Notificación a usuarios afectados
4. **Roles Temporales**: Soporte para roles con fecha de expiración
5. **Backup de Configuración**: Exportación de configuración de roles

## Modelo de Autorización
```
Super Admin > Admin > Manager > User > Guest
```

Cada nivel incluye permisos del nivel inferior más permisos específicos.

## Métricas de Éxito
- 100% de operaciones de roles auditadas
- 0% de violaciones de seguridad por roles
- Tiempo de respuesta < 1 segundo para operaciones críticas
- Tasa de error de configuración < 1%
- Satisfacción de administradores > 90%

## Historial de Cambios
- **v1.0** - Implementación básica de gestión de roles (02/10/2025)
- **v1.1** - Agregado control de jerarquía de roles (02/10/2025)
- **v1.2** - Implementado middleware de autorización (02/10/2025)
- **v1.3** - Agregado sistema de auditoría completo (02/10/2025)</content>
<parameter name="filePath">c:\Users\PG\Desktop\Materias\Sistemas de Informacion 1\Grupo SC\proyecto_confeccion\modas_boom\Docs\Ciclo 1\CU4_Gestionar_Roles.md