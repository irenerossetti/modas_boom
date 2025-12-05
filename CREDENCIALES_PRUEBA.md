# 🔐 Credenciales de Prueba - Modas Boom

## ✅ USUARIOS ACTUALIZADOS Y FUNCIONANDO

### 1. 👨‍💼 Administrador (Super Admin)
```
Email:    super@boom.com
Password: clave123
Rol:      Administrador (ID: 1)
```

**Permisos**: Acceso total al sistema

---

### 2. 👔 Empleado/Trabajador
```
Email:    empleado@boom.com
Password: clave123
Rol:      Empleado (ID: 2)
```

**Permisos**: Gestión de pedidos y producción

---

### 3. 🛍️ Cliente
```
Email:    cliente@boom.com
Password: clave123
Rol:      Cliente (ID: 3)
```

**Permisos**: Portal de clientes con barra de progreso visual

---

## 🚀 Para Iniciar Sesión

1. Ve a: `http://localhost:8000/login`
2. Usa cualquiera de las credenciales de arriba
3. Explora el sistema según el rol

---

## 🔄 Si no funcionan las credenciales

Ejecuta en la terminal:

```bash
cd modas_boom
php artisan db:seed --class=UsuarioSeeder
```

Esto recreará los usuarios de prueba.

---

## 📝 Notas

- Todos los usuarios usan la contraseña: `clave123`
- Los usuarios se crean automáticamente al ejecutar el seeder
- Si cambias el `.env`, ejecuta el seeder nuevamente

---

**Última actualización**: 4 de diciembre de 2025  
**Estado**: ✅ Usuarios creados y verificados
