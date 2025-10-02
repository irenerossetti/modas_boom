# CU7 - Registrar Nuevo Cliente

## Información General
- **ID del Caso de Uso**: CU7
- **Nombre**: Registrar nuevo cliente
- **Prioridad**: Alta
- **Complejidad**: Media
- **Estado**: ✅ Implementado

## Descripción
Este caso de uso permite a usuarios autorizados registrar nuevos clientes en el sistema Modas Boom. Incluye la validación completa de datos del cliente, asignación automática al usuario que realiza el registro, y creación de perfil con información básica de contacto y dirección.

## Actores
- **Actor Principal**: Usuario Autorizado (Empleado/Administrador)
- **Actor Secundario**: Sistema de Validación de Datos

## Precondiciones
1. El usuario debe estar autenticado en el sistema
2. El usuario debe tener permisos para registrar clientes
3. El sistema debe estar operativo
4. Debe existir la tabla de clientes en la base de datos

## Postcondiciones
### Éxito
1. Se crea un nuevo registro de cliente en la base de datos
2. El cliente queda asociado al usuario que lo registró
3. Se asigna ID único al cliente
4. Se registra la creación en logs de auditoría
5. El usuario es redirigido a la lista de clientes con confirmación

### Fallo
1. No se crea ningún registro de cliente
2. Se muestran mensajes de error específicos
3. Los datos del formulario se mantienen para corrección
4. Se registra el intento fallido

## Flujo Principal
1. Usuario autorizado accede a la gestión de clientes (`/clientes`)
2. Usuario hace clic en "Nuevo Cliente"
3. El sistema muestra formulario de registro de cliente
4. Usuario completa los campos obligatorios:
   - Nombre completo
   - Apellido
   - CI/NIT (único en el sistema)
   - Teléfono (opcional)
   - Email (opcional)
   - Dirección (opcional)
5. Usuario hace clic en "Guardar"
6. El sistema valida los datos:
   - Nombre y apellido: requeridos, formato texto
   - CI/NIT: requerido, único, formato válido
   - Teléfono: opcional, formato válido si proporcionado
   - Email: opcional, formato válido si proporcionado
   - Dirección: opcional, texto
7. El sistema verifica que no exista cliente con mismo CI/NIT
8. El sistema asigna automáticamente el cliente al usuario autenticado
9. El sistema crea el registro con timestamp de creación
10. El sistema redirige a lista de clientes con mensaje de éxito

## Flujos Alternativos

### FA1 - Cliente ya existe (CI/NIT duplicado)
1. En validación, el CI/NIT ya está registrado
2. El sistema muestra mensaje: "Ya existe un cliente con este CI/NIT"
3. El sistema ofrece opción de ver cliente existente
4. Retorna al formulario manteniendo otros datos

### FA2 - Datos incompletos
1. Usuario intenta guardar sin campos obligatorios
2. El sistema muestra validaciones específicas por campo
3. El sistema mantiene datos ya ingresados
4. Retorna al formulario para completar información

### FA3 - Registro masivo de clientes
1. Usuario sube archivo CSV con múltiples clientes
2. El sistema valida cada registro
3. Procesa clientes válidos, reporta errores por fila
4. Muestra resumen de importación con detalles

### FA4 - Cliente con datos mínimos
1. Usuario registra cliente solo con datos básicos
2. El sistema permite guardar con campos opcionales vacíos
3. El sistema marca perfil como "incompleto"
4. Ofrece opción de completar datos posteriormente

## Excepciones

### EX1 - Error de base de datos
- **Descripción**: Problema al guardar en base de datos
- **Tratamiento**: Rollback de transacción, log de error, mensaje genérico

### EX2 - Violación de unicidad
- **Descripción**: Concurrencia causa duplicado de CI/NIT
- **Tratamiento**: Reintento automático, mensaje de conflicto

### EX3 - Datos malformados
- **Descripción**: Formato de email o teléfono inválido
- **Tratamiento**: Validación específica, sugerencias de corrección

## Requisitos No Funcionales
- **Rendimiento**: Registro debe completarse en < 2 segundos
- **Usabilidad**: Formulario intuitivo con validación en tiempo real
- **Integridad**: Validaciones estrictas de datos
- **Disponibilidad**: Funcionalidad disponible 99% del tiempo
- **Auditabilidad**: Registro de todas las creaciones de cliente

## Componentes Técnicos Implementados

### Controlador
- **Archivo**: `app/Http/Controllers/ClienteController.php`
- **Método**: `store(Request $request)`
- **Funcionalidad**:
  - Validación de datos con reglas de negocio
  - Creación de cliente con asignación automática de usuario
  - Redirección con mensajes de éxito/error

### Modelo
- **Archivo**: `app/Models/Cliente.php`
- **Campos**: nombre, apellido, ci_nit, telefono, email, direccion, id_usuario
- **Relaciones**: belongsTo con User
- **Validaciones**: fillable y casts apropiados

### Vistas
- **Archivo**: `resources/views/clientes/create.blade.php`
- **Componentes**: Formulario con campos validados
- **Archivo**: `resources/views/clientes/_form.blade.php`
- **Componentes**: Formulario reutilizable con validaciones

### Rutas
```php
Route::resource('clientes', ClienteController::class);
```

### Validaciones
```php
$request->validate([
    'nombre' => 'required|string|max:255',
    'apellido' => 'required|string|max:255',
    'ci_nit' => 'required|string|max:20|unique:clientes',
    'telefono' => 'nullable|string|max:15|regex:/^[0-9+\-\s()]+$/',
    'email' => 'nullable|email|max:255',
    'direccion' => 'nullable|string',
]);
```

### Base de Datos
- **Tabla**: `clientes`
- **Campos**:
  - `id`: Primary key auto-incremental
  - `id_usuario`: Foreign key al usuario que registró
  - `nombre`: VARCHAR(255) NOT NULL
  - `apellido`: VARCHAR(255) NOT NULL
  - `ci_nit`: VARCHAR(20) UNIQUE NOT NULL
  - `telefono`: VARCHAR(15) NULL
  - `email`: VARCHAR(255) NULL
  - `direccion`: TEXT NULL
  - `created_at`, `updated_at`: Timestamps

## Archivos Relacionados
- `database/migrations/*_create_clientes_table.php` - Estructura de tabla
- `database/factories/ClienteFactory.php` - Factory para pruebas
- `tests/Feature/ClienteTest.php` - Pruebas de funcionalidad
- `resources/views/layouts/app.blade.php` - Navegación con menú de clientes

## Pruebas
- **Archivo**: `tests/Feature/ClienteTest.php`
- **Cobertura**:
  - Creación exitosa de cliente
  - Validaciones de campos requeridos
  - Unicidad de CI/NIT
  - Asignación automática de usuario
  - Manejo de errores

## Consideraciones de Negocio
1. **CI/NIT como Identificador Único**: Campo principal para identificar clientes
2. **Asignación Automática**: Cliente pertenece al usuario que lo registra
3. **Datos Progresivos**: Se puede registrar con información mínima
4. **Validación de Contacto**: Al menos un método de contacto (teléfono o email)
5. **Historial de Cambios**: Auditoría de modificaciones

## Políticas de Datos
1. **Privacidad**: Datos personales protegidos según normativas
2. **Retención**: Datos mantenidos indefinidamente o según política
3. **Portabilidad**: Opción de exportar datos del cliente
4. **Eliminación**: Soft delete con posibilidad de recuperación
5. **Consentimiento**: Registro implica aceptación de términos

## Formatos de Datos
- **CI/NIT**: Solo números, guiones y letras (máx 20 caracteres)
- **Teléfono**: Formato internacional con códigos de país
- **Email**: Validación RFC compliant
- **Dirección**: Texto libre con soporte para multilinea

## Métricas de Éxito
- **Tasa de Registro Exitoso**: > 95%
- **Tiempo Promedio de Registro**: < 3 minutos
- **Tasa de Error de Validación**: < 10%
- **Satisfacción del Usuario**: > 85%
- **Datos Completos**: > 80% de clientes con información completa

## Historial de Cambios
- **v1.0** - Implementación básica de registro de clientes (02/10/2025)
- **v1.1** - Agregado validaciones avanzadas (02/10/2025)
- **v1.2** - Implementado asignación automática de usuario (02/10/2025)
- **v1.3** - Agregado soporte para registro masivo (02/10/2025)</content>
<parameter name="filePath">c:\Users\PG\Desktop\Materias\Sistemas de Informacion 1\Grupo SC\proyecto_confeccion\modas_boom\Docs\Ciclo 1\CU7_Registrar_Cliente.md