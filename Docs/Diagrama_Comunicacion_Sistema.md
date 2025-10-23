# Diagrama de Comunicación - Sistema Modas Boom

## 📋 Arquitectura General del Sistema

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   FRONTEND      │    │   BACKEND       │    │   DATABASE      │
│   (Blade Views) │◄──►│   (Laravel)     │◄──►│   (SQLite)      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🔄 Flujo de Comunicación Principal

### 1. Autenticación y Autorización
```
Usuario → Middleware → Controlador → Modelo → Base de Datos
   ↓         ↓           ↓           ↓          ↓
 Login → CheckAuth → UserController → User → usuarios/rol
   ↓         ↓           ↓           ↓          ↓
Response ← Vista ← Redirect ← Validación ← Consulta
```

### 2. Gestión de Pedidos
```
Cliente/Empleado → PedidoController → Prenda/Cliente → SQLite
       ↓               ↓                 ↓             ↓
   Formulario → clienteStore() → descontarStock() → UPDATE
       ↓               ↓                 ↓             ↓
   Respuesta ← Vista ← Pedido ← Transacción ← pedido_prenda
```

### 3. Control de Stock
```
Crear Pedido → Verificar Stock → Descontar → Crear Relación
     ↓              ↓              ↓           ↓
PedidoController → Prenda → tieneStock() → pedido_prenda
     ↓              ↓              ↓           ↓
Cancelar Pedido → Restaurar Stock → increment() → UPDATE
```

## 🏗️ Componentes del Sistema

### Controladores Principales
```
┌─────────────────────────────────────────────────────────┐
│                    CONTROLADORES                        │
├─────────────────┬─────────────────┬─────────────────────┤
│ UserController  │ RolController   │ PedidoController    │
│ - index()       │ - index()       │ - clienteStore()    │
│ - store()       │ - edit()        │ - show()            │
│ - update()      │ - update()      │ - destroy()         │
│ - destroy()     │ - destroy()     │ - misPedidos()      │
├─────────────────┼─────────────────┼─────────────────────┤
│ ClienteController│ PrendaController│ CatalogoController  │
│ - index()       │ - index()       │ - index()           │
│ - store()       │ - store()       │ - crearPedido()     │
│ - update()      │ - update()      │ - pedidoConfirmado()│
│ - destroy()     │ - destroy()     │                     │
└─────────────────┴─────────────────┴─────────────────────┘
```

### Modelos y Relaciones
```
┌─────────────────────────────────────────────────────────┐
│                      MODELOS                            │
├─────────────────┬─────────────────┬─────────────────────┤
│     User        │      Rol        │     Cliente         │
│ - id_usuario    │ - id_rol        │ - id                │
│ - nombre        │ - nombre        │ - nombre            │
│ - email         │ - descripcion   │ - apellido          │
│ - id_rol        │ - habilitado    │ - email             │
│ - habilitado    │                 │ - ci_nit            │
├─────────────────┼─────────────────┼─────────────────────┤
│     Pedido      │     Prenda      │   pedido_prenda     │
│ - id_pedido     │ - id            │ - pedido_id         │
│ - id_cliente    │ - nombre        │ - prenda_id         │
│ - estado        │ - precio        │ - cantidad          │
│ - total         │ - stock         │ - precio_unitario   │
│ - created_at    │ - categoria     │ - talla             │
│                 │ - activo        │ - color             │
└─────────────────┴─────────────────┴─────────────────────┘
```

### Middleware de Seguridad
```
┌─────────────────────────────────────────────────────────┐
│                    MIDDLEWARE                           │
├─────────────────┬─────────────────┬─────────────────────┤
│ CheckUserEnabled│ CheckAdminRole  │ HandleCsrfErrors    │
│ - Verifica      │ - Solo Admin    │ - Protección CSRF   │
│   usuario       │   (id_rol = 1)  │ - Manejo errores    │
│   habilitado    │ - Redirige      │ - Regenera token    │
│                 │   según rol     │                     │
├─────────────────┼─────────────────┼─────────────────────┤
│ LoginAttempt    │ Authenticate    │ AuditoriaMiddleware │
│ Throttle        │ - Verifica      │ - Registra          │
│ - Limita        │   autenticación │   actividades       │
│   intentos      │ - Redirige      │ - Bitácora          │
│   login         │   a login       │   sistema           │
└─────────────────┴─────────────────┴─────────────────────┘
```

## 🔐 Flujo de Autenticación

```
1. Usuario ingresa credenciales
   ↓
2. LoginAttemptThrottle (máx 5 intentos)
   ↓
3. Authenticate middleware
   ↓
4. CheckUserEnabled (usuario habilitado?)
   ↓
5. CheckAdminRole (si ruta protegida)
   ↓
6. Controlador ejecuta acción
   ↓
7. Modelo consulta base de datos
   ↓
8. Vista renderiza respuesta
```

## 📊 Flujo de Gestión de Pedidos

```
CREAR PEDIDO:
Cliente → Formulario → PedidoController.clienteStore()
   ↓
Validar datos → Verificar stock → Crear transacción
   ↓
Descontar stock → Crear pedido → Crear relación pivot
   ↓
Registrar bitácora → Respuesta exitosa

CANCELAR PEDIDO:
Admin → PedidoController.destroy() → Verificar permisos
   ↓
Cargar prendas → Restaurar stock → Cambiar estado
   ↓
Registrar bitácora → Respuesta exitosa
```

## 🛡️ Seguridad y Permisos

### Matriz de Permisos por Rol
```
┌─────────────────┬─────────────┬─────────────┬─────────────┐
│    FUNCIÓN      │ ADMIN (1)   │ EMPLEADO(2) │ CLIENTE(3)  │
├─────────────────┼─────────────┼─────────────┼─────────────┤
│ Gestionar Users │     ✅      │     ❌      │     ❌      │
│ Gestionar Roles │     ✅      │     👁️      │     ❌      │
│ Gestionar Prendas│     ✅      │     ❌      │     ❌      │
│ Ver Clientes    │     ✅      │     ✅      │     ❌      │
│ Crear Clientes  │     ✅      │     ❌      │     ❌      │
│ Crear Pedidos   │     ✅      │     ✅      │     ✅      │
│ Ver Todos Pedidos│     ✅      │     ✅      │     ❌      │
│ Ver Mis Pedidos │     ✅      │     ✅      │     ✅      │
│ Cancelar Pedidos│     ✅      │     ❌      │     ❌      │
│ Ver Bitácora    │     ✅      │     ❌      │     ❌      │
└─────────────────┴─────────────┴─────────────┴─────────────┘

Leyenda: ✅ Acceso completo | 👁️ Solo lectura | ❌ Sin acceso
```

## 🔄 Comunicación Entre Componentes

### Patrón MVC Implementado
```
VISTA (Blade) ←→ CONTROLADOR (Laravel) ←→ MODELO (Eloquent) ←→ BD (SQLite)
      ↑                    ↑                     ↑              ↑
   Formularios         Validación           Relaciones      Consultas
   Respuestas          Lógica Negocio       Scopes          Transacciones
   Navegación          Middleware           Mutators        Índices
```

### Servicios Auxiliares
```
┌─────────────────────────────────────────────────────────┐
│                    SERVICIOS                            │
├─────────────────┬─────────────────┬─────────────────────┤
│ BitacoraService │ Cache System    │ Validation Rules    │
│ - Registra      │ - productos_    │ - Formularios       │
│   actividades   │   catalogo_db   │ - Campos únicos     │
│ - Auditoría     │ - Optimización  │ - Reglas negocio    │
│   sistema       │   consultas     │                     │
└─────────────────┴─────────────────┴─────────────────────┘
```

## 📱 Interfaces de Usuario por Rol

### Administrador
```
Dashboard → Usuarios → Roles → Prendas → Clientes → Pedidos → Bitácora
    ↓         ↓        ↓       ↓         ↓          ↓         ↓
  Stats    CRUD     CRUD    CRUD      CRUD       CRUD     Logs
```

### Empleado
```
Dashboard → Clientes → Pedidos → Hacer Pedido Personal
    ↓         ↓         ↓            ↓
  Stats    Ver/Buscar  CRUD      Crear propio
```

### Cliente
```
Catálogo → Hacer Pedido → Mis Pedidos
    ↓          ↓            ↓
  Browse    Crear        Ver propios
```

## 🚀 Optimizaciones Implementadas

### Performance
- **Cache:** productos_catalogo_db (1 hora)
- **Índices:** Base de datos optimizada
- **Eager Loading:** Relaciones precargadas
- **Paginación:** Listados grandes

### Seguridad
- **CSRF Protection:** Todos los formularios
- **Rate Limiting:** Login attempts
- **Middleware Stack:** Múltiples capas
- **Validación:** Frontend y backend

---

*Este diagrama representa la arquitectura actual del sistema Modas Boom con todos sus componentes y flujos de comunicación implementados.*