# CU8 - Gestionar Información de Clientes

## Información General
- **ID del Caso de Uso**: CU8
- **Nombre**: Gestionar información de clientes
- **Prioridad**: Alta
- **Complejidad**: Alta
- **Estado**: ✅ Implementado

## Descripción
Este caso de uso permite a usuarios autorizados realizar operaciones CRUD completas sobre la información de clientes en el sistema Modas Boom. Incluye listar, crear, editar, eliminar y ver detalles de clientes, con validaciones de seguridad, controles de acceso y mantenimiento de integridad de datos.

## Actores
- **Actor Principal**: Usuario Autorizado (Empleado/Administrador)
- **Actor Secundario**:
  - Sistema de Base de Datos
  - Sistema de Validación
  - Sistema de Auditoría

## Precondiciones
1. El usuario debe estar autenticado
2. El usuario debe tener permisos para gestionar clientes
3. Debe existir al menos un cliente registrado
4. El sistema debe estar operativo

## Postcondiciones
### Éxito - Operación CRUD
1. Los datos del cliente quedan actualizados según la operación
2. Se mantiene la integridad referencial
3. Se registra la operación en auditoría
4. Se muestra confirmación al usuario

### Fallo
1. No se modifica ningún dato
2. Se muestra mensaje de error específico
3. Los datos del formulario se preservan
4. Se registra el intento fallido

## Flujo Principal - Listar Clientes
1. Usuario accede a gestión de clientes (`/clientes`)
2. Sistema verifica permisos de acceso
3. Sistema recupera lista de clientes con paginación
4. Sistema muestra tabla con información resumida:
   - ID, Nombre completo, CI/NIT, Email, Teléfono, Acciones
5. Usuario puede navegar entre páginas
6. Usuario puede usar búsqueda/filtros

## Flujo Principal - Crear Cliente
1. Usuario hace clic en "Nuevo Cliente"
2. Sistema muestra formulario de creación
3. Usuario completa campos requeridos y opcionales
4. Usuario hace clic en "Crear"
5. Sistema valida todos los datos
6. Sistema crea cliente con asignación automática de usuario
7. Sistema redirige con mensaje de éxito

## Flujo Principal - Ver Detalles de Cliente
1. Usuario selecciona "Ver" en un cliente
2. Sistema muestra información completa del cliente
3. Se incluye información del usuario que lo registró
4. Usuario puede navegar a edición o eliminación

## Flujo Principal - Editar Cliente
1. Usuario selecciona "Editar" en un cliente
2. Sistema carga datos actuales del cliente
3. Usuario modifica campos necesarios
4. Usuario hace clic en "Actualizar"
5. Sistema valida cambios (especialmente unicidad de CI/NIT)
6. Sistema actualiza información
7. Sistema redirige con confirmación

## Flujo Principal - Eliminar Cliente
1. Usuario selecciona "Eliminar" en un cliente
2. Sistema muestra confirmación de eliminación
3. Usuario confirma la acción
4. Sistema verifica restricciones de integridad
5. Sistema elimina cliente (soft delete)
6. Sistema redirige con mensaje de éxito

## Flujos Alternativos

### FA1 - Cliente con Pedidos Asociados
1. En eliminación, cliente tiene pedidos relacionados
2. Sistema muestra mensaje: "Cliente tiene pedidos asociados"
3. Sistema ofrece opciones:
   - Transferir pedidos a otro cliente
   - Cancelar eliminación
4. Si se transfiere: continúa eliminación

### FA2 - Edición de CI/NIT
1. Usuario intenta cambiar CI/NIT de cliente
2. Sistema valida que nuevo CI/NIT no exista
3. Si existe: muestra error y mantiene valor original
4. Si no existe: permite cambio con confirmación adicional

### FA3 - Búsqueda Avanzada
1. Usuario utiliza filtros de búsqueda
2. Sistema aplica filtros por nombre, CI/NIT, email
3. Sistema muestra resultados paginados
4. Usuario puede exportar resultados

### FA4 - Importación/Exportación Masiva
1. Usuario sube archivo con múltiples clientes
2. Sistema valida y procesa cada registro
3. Sistema muestra resumen con errores y éxitos
4. Usuario puede descargar plantilla de importación

## Excepciones

### EX1 - Concurrencia de Edición
- **Descripción**: Múltiples usuarios editando simultáneamente
- **Tratamiento**: Detección de conflictos, mostrar diferencias, permitir resolución

### EX2 - Violación de Integridad
- **Descripción**: Intento de eliminar cliente con dependencias
- **Tratamiento**: Mostrar dependencias, sugerir acciones correctivas

### EX3 - Error de Validación Masiva
- **Descripción**: Importación con muchos errores
- **Tratamiento**: Procesar válidos, reportar errores detalladamente

## Requisitos No Funcionales
- **Rendimiento**: Listado con paginación < 1 segundo
- **Escalabilidad**: Soporte para miles de clientes
- **Usabilidad**: Interfaz intuitiva con validaciones en tiempo real
- **Seguridad**: Control de acceso por roles
- **Auditabilidad**: 100% de operaciones registradas

## Componentes Técnicos Implementados

### Controlador Principal
- **Archivo**: `app/Http/Controllers/ClienteController.php`
- **Métodos**:
  - `index()`: Lista con paginación y búsqueda
  - `create()`: Formulario de creación
  - `store()`: Crear cliente con validaciones
  - `show()`: Detalles del cliente
  - `edit()`: Formulario de edición
  - `update()`: Actualizar cliente
  - `destroy()`: Eliminar cliente con verificaciones

### Modelo de Cliente
- **Archivo**: `app/Models/Cliente.php`
- **Relaciones**: belongsTo con User
- **Campos**: Todos los campos de cliente con validaciones
- **Funcionalidad**: Soft deletes, fillable, casts

### Vistas del Sistema
- **Archivos**:
  - `resources/views/clientes/index.blade.php` - Lista con búsqueda
  - `resources/views/clientes/create.blade.php` - Crear cliente
  - `resources/views/clientes/edit.blade.php` - Editar cliente
  - `resources/views/clientes/show.blade.php` - Detalles cliente
  - `resources/views/clientes/_form.blade.php` - Formulario reutilizable

### Rutas y Middleware
```php
Route::middleware(['auth', 'user.enabled'])->group(function () {
    Route::resource('clientes', ClienteController::class);
});
```

### Validaciones Implementadas
```php
// Creación
'nombre' => 'required|string|max:255',
'apellido' => 'required|string|max:255',
'ci_nit' => 'required|string|max:20|unique:clientes',
'telefono' => 'nullable|string|max:15',
'email' => 'nullable|email|max:255',
'direccion' => 'nullable|string',

// Edición
'nombre' => 'required|string|max:255',
'apellido' => 'required|string|max:255',
'ci_nit' => 'required|string|max:20|unique:clientes,ci_nit,'.$cliente->id,
'telefono' => 'nullable|string|max:15',
'email' => 'nullable|email|max:255',
'direccion' => 'nullable|string',
```

### Sistema de Búsqueda
- **Funcionalidad**: Búsqueda por nombre, apellido, CI/NIT
- **Implementación**: Query builder con LIKE y paginación
- **Rendimiento**: Índices en campos de búsqueda

## Archivos Relacionados
- `database/migrations/*_create_clientes_table.php` - Estructura base
- `database/migrations/*_add_id_usuario_to_clientes_table.php` - Relación usuario
- `database/factories/ClienteFactory.php` - Datos de prueba
- `tests/Feature/ClienteTest.php` - Suite de pruebas
- `app/Services/ClienteService.php` - Lógica de negocio adicional

## Pruebas del Sistema
- **Archivo**: `tests/Feature/ClienteTest.php`
- **Cobertura**:
  - CRUD completo de clientes
  - Validaciones de campos
  - Búsqueda y filtrado
  - Integridad referencial
  - Autorización de operaciones

## Consideraciones de Seguridad
1. **Autorización**: Verificación de permisos por operación
2. **Validación**: Datos sanitizados y validados en servidor
3. **Auditoría**: Registro de todas las operaciones
4. **Soft Delete**: Eliminaciones no destructivas
5. **Rate Limiting**: Control de operaciones masivas

## Políticas de Gestión
1. **Unicidad**: CI/NIT como identificador único
2. **Asignación**: Cliente pertenece al usuario que lo registra
3. **Privacidad**: Datos protegidos según normativas
4. **Retención**: Política de retención de datos configurable
5. **Backup**: Copias de seguridad automáticas

## Funcionalidades Avanzadas
1. **Búsqueda en Tiempo Real**: Autocomplete en campos de búsqueda
2. **Filtros Avanzados**: Por fecha, usuario, estado
3. **Exportación**: PDF, Excel, CSV de listados
4. **Importación**: Carga masiva con validación
5. **Historial**: Tracking de cambios por cliente

## Métricas de Éxito
- **Tasa de Operaciones Exitosas**: > 98%
- **Tiempo de Respuesta**: < 2 segundos para operaciones críticas
- **Satisfacción del Usuario**: > 90%
- **Tasa de Error de Datos**: < 2%
- **Disponibilidad**: 99.5%

## Historial de Cambios
- **v1.0** - Implementación básica CRUD (02/10/2025)
- **v1.1** - Agregado sistema de búsqueda (02/10/2025)
- **v1.2** - Implementado soft delete y auditoría (02/10/2025)
- **v1.3** - Agregado importación/exportación masiva (02/10/2025)
- **v1.4** - Implementado filtros avanzados y paginación (02/10/2025)</content>
<parameter name="filePath">c:\Users\PG\Desktop\Materias\Sistemas de Informacion 1\Grupo SC\proyecto_confeccion\modas_boom\Docs\Ciclo 1\CU8_Gestionar_Clientes.md