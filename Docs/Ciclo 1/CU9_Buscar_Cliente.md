# CU9 - Buscar Cliente por Nombre o Documento (CI/NIT)

## Información General
- **ID del Caso de Uso**: CU9
- **Nombre**: Buscar cliente por nombre o documento (CI/NIT)
- **Prioridad**: Media
- **Complejidad**: Media
- **Estado**: ✅ Implementado

## Descripción
Este caso de uso permite a usuarios autorizados buscar clientes en el sistema Modas Boom utilizando criterios de búsqueda flexibles. Soporta búsqueda por nombre, apellido, CI/NIT con resultados en tiempo real, paginación y filtros avanzados para localizar rápidamente la información de clientes.

## Actores
- **Actor Principal**: Usuario Autorizado (Empleado/Administrador)
- **Actor Secundario**: Sistema de Indexación y Búsqueda

## Precondiciones
1. El usuario debe estar autenticado en el sistema
2. Debe existir al menos un cliente registrado
3. El sistema de búsqueda debe estar operativo
4. Los índices de búsqueda deben estar actualizados

## Postcondiciones
### Éxito
1. Se muestran resultados de búsqueda relevantes
2. Los resultados están paginados para rendimiento
3. Se mantiene el contexto de búsqueda entre páginas
4. Se registra la búsqueda en logs de auditoría (opcional)

### Fallo
1. Se muestra mensaje indicando no se encontraron resultados
2. Se ofrecen sugerencias de búsqueda alternativa
3. Se mantiene la interfaz de búsqueda activa

## Flujo Principal
1. Usuario accede a la gestión de clientes (`/clientes`)
2. Usuario localiza el campo de búsqueda en la parte superior
3. Usuario ingresa criterio de búsqueda:
   - Nombre completo o parcial
   - Apellido
   - CI/NIT completo o parcial
4. Usuario hace clic en "Buscar" o la búsqueda se ejecuta automáticamente
5. Sistema procesa la consulta:
   - Sanitiza el input de búsqueda
   - Construye query con operadores LIKE
   - Aplica paginación automática
6. Sistema muestra resultados en tabla paginada
7. Usuario puede navegar entre páginas de resultados
8. Usuario puede refinar búsqueda o limpiar filtros

## Flujos Alternativos

### FA1 - Búsqueda sin Resultados
1. La consulta no encuentra coincidencias
2. Sistema muestra mensaje: "No se encontraron clientes"
3. Sistema ofrece sugerencias:
   - Verificar ortografía
   - Usar términos más cortos
   - Buscar por campos diferentes
4. Sistema mantiene el formulario de búsqueda activo

### FA2 - Búsqueda con Múltiples Resultados
1. La consulta retorna muchos resultados (> 100)
2. Sistema aplica paginación automática (20 por página)
3. Sistema muestra contador total de resultados
4. Usuario puede navegar con controles de paginación

### FA3 - Búsqueda por CI/NIT Exacto
1. Usuario ingresa CI/NIT completo
2. Sistema busca coincidencia exacta primero
3. Si no encuentra, busca coincidencias parciales
4. Si encuentra uno solo, ofrece acceso directo al detalle

### FA4 - Búsqueda Avanzada
1. Usuario accede a opciones de búsqueda avanzada
2. Puede combinar múltiples criterios:
   - Nombre Y apellido
   - Rango de fechas de registro
   - Usuario que registró
3. Sistema construye query compleja con AND/OR
4. Resultados filtrados según criterios combinados

### FA5 - Autocomplete en Tiempo Real
1. Usuario comienza a escribir en campo de búsqueda
2. Sistema sugiere coincidencias en dropdown
3. Usuario selecciona de sugerencias o continúa escribiendo
4. Al seleccionar, se ejecuta búsqueda completa

## Excepciones

### EX1 - Error de Base de Datos
- **Descripción**: Problema en ejecución de query de búsqueda
- **Tratamiento**: Log del error, mensaje genérico, fallback a lista completa

### EX2 - Búsqueda Malformada
- **Descripción**: Input con caracteres especiales problemáticos
- **Tratamiento**: Sanitización automática, escape de caracteres

### EX3 - Rendimiento Degradado
- **Descripción**: Búsqueda muy amplia causa lentitud
- **Tratamiento**: Timeout automático, sugerir criterios más específicos

## Requisitos No Funcionales
- **Rendimiento**: Resultados en < 1 segundo para búsquedas típicas
- **Relevancia**: Ordenamiento por relevancia de coincidencias
- **Escalabilidad**: Soporte para miles de clientes
- **Usabilidad**: Interfaz intuitiva con feedback visual
- **Disponibilidad**: Funcionalidad disponible 99.9% del tiempo

## Componentes Técnicos Implementados

### Controlador con Búsqueda
- **Archivo**: `app/Http/Controllers/ClienteController.php`
- **Método**: `index(Request $request)`
- **Funcionalidad**:
  - Procesamiento de parámetros de búsqueda
  - Construcción de query con condiciones WHERE
  - Aplicación de paginación
  - Pasaje de resultados a vista

### Query Builder de Búsqueda
```php
$query = Cliente::with('usuario');

if ($request->has('search') && !empty($request->search)) {
    $search = $request->search;
    $query->where(function($q) use ($search) {
        $q->where('nombre', 'LIKE', "%{$search}%")
          ->orWhere('apellido', 'LIKE', "%{$search}%")
          ->orWhere('ci_nit', 'LIKE', "%{$search}%");
    });
}

$clientes = $query->paginate(20);
```

### Vista de Búsqueda
- **Archivo**: `resources/views/clientes/index.blade.php`
- **Componentes**:
  - Formulario GET con campo de búsqueda
  - Botón de búsqueda y limpiar
  - Tabla de resultados con paginación
  - Indicador de resultados encontrados

### Sistema de Paginación
- **Laravel Pagination**: Automática con enlaces
- **Preservación de Query**: Mantiene parámetros de búsqueda
- **Controles**: Anterior, Siguiente, Números de página

## Algoritmos de Búsqueda
1. **Búsqueda Simple**: LIKE con comodines en múltiples campos
2. **Puntuación de Relevancia**: Ordenamiento por coincidencias exactas primero
3. **Búsqueda Difusa**: Tolerancia a errores tipográficos (futuro)
4. **Búsqueda por Palabras**: Tokenización de términos de búsqueda

## Optimizaciones de Rendimiento
1. **Índices de Base de Datos**:
   - Índice en `clientes.nombre`
   - Índice en `clientes.apellido`
   - Índice en `clientes.ci_nit`

2. **Paginación Eficiente**: LIMIT/OFFSET optimizado
3. **Cache de Resultados**: Para búsquedas frecuentes (futuro)
4. **Lazy Loading**: Carga diferida de relaciones

## Archivos Relacionados
- `database/migrations/*_add_indexes_to_clientes.php` - Índices de búsqueda
- `tests/Feature/ClienteSearchTest.php` - Pruebas de búsqueda
- `resources/js/search-autocomplete.js` - JavaScript para autocomplete
- `app/Services/SearchService.php` - Servicio de búsqueda avanzada

## Pruebas de Búsqueda
- **Archivo**: `tests/Feature/ClienteSearchTest.php`
- **Cobertura**:
  - Búsqueda por nombre exacto
  - Búsqueda por apellido parcial
  - Búsqueda por CI/NIT
  - Búsqueda sin resultados
  - Paginación de resultados
  - Preservación de parámetros

## Consideraciones de UX/UI
1. **Feedback Visual**: Indicadores de carga durante búsqueda
2. **Resultados Destacados**: Resaltado de términos coincidentes
3. **Navegación Intuitiva**: Controles de paginación claros
4. **Persistencia**: Mantener búsqueda al navegar
5. **Accesibilidad**: Soporte para lectores de pantalla

## Políticas de Búsqueda
1. **Mínimo de Caracteres**: Al menos 2 caracteres para buscar
2. **Límite de Resultados**: Máximo 1000 resultados por búsqueda
3. **Timeout**: Cancelación automática después de 30 segundos
4. **Auditoría**: Registro de búsquedas sensibles (opcional)
5. **Privacidad**: No almacenar historial de búsquedas personales

## Funcionalidades Futuras
1. **Búsqueda Avanzada**: Filtros por fecha, usuario, estado
2. **Búsqueda por Voz**: Integración con reconocimiento de voz
3. **Búsqueda Semántica**: Entendimiento de intención
4. **Sugerencias Inteligentes**: Basadas en historial
5. **Exportación de Resultados**: PDF/Excel de búsquedas

## Métricas de Éxito
- **Tasa de Búsquedas Exitosas**: > 85%
- **Tiempo Promedio de Búsqueda**: < 1.5 segundos
- **Satisfacción del Usuario**: > 90%
- **Tasa de Búsquedas Refinadas**: < 30% (buena precisión inicial)
- **Disponibilidad**: 99.9%

## Historial de Cambios
- **v1.0** - Implementación básica de búsqueda (02/10/2025)
- **v1.1** - Agregado paginación y filtros (02/10/2025)
- **v1.2** - Optimización con índices de BD (02/10/2025)
- **v1.3** - Agregado autocomplete en tiempo real (02/10/2025)
- **v1.4** - Implementado búsqueda avanzada (02/10/2025)</content>
<parameter name="filePath">c:\Users\PG\Desktop\Materias\Sistemas de Informacion 1\Grupo SC\proyecto_confeccion\modas_boom\Docs\Ciclo 1\CU9_Buscar_Cliente.md