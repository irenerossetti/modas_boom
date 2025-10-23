# 🛍️ Configuración de Productos - Modas Boom

## ⚠️ IMPORTANTE: Ejecutar después de clonar el proyecto

Si acabas de clonar el proyecto y no ves productos en el catálogo, sigue estos pasos:

## 🚀 Configuración Rápida

### Opción 1: Script Automático
```bash
php setup_productos.php
```

### Opción 2: Comandos Manuales
```bash
# 1. Ejecutar migraciones
php artisan migrate

# 2. Crear productos en la base de datos
php artisan db:seed --class=PrendaSeeder

# 3. Limpiar cache
php artisan cache:clear
```

## ✅ Verificación

Después de ejecutar los comandos, deberías ver:
- ✅ **9 productos** en el catálogo
- ✅ **2 categorías**: Formal e Informal
- ✅ **Imágenes** cargando correctamente
- ✅ **Filtros** funcionando

## 🔍 Comandos de Verificación

```bash
# Ver cuántos productos hay
php artisan tinker --execute="echo 'Productos: ' . App\Models\Prenda::count();"

# Ver nombres de productos
php artisan tinker --execute="App\Models\Prenda::all()->pluck('nombre')->each(function(\$nombre) { echo \$nombre . PHP_EOL; });"

# Ver categorías disponibles
php artisan tinker --execute="App\Models\Prenda::distinct()->pluck('categoria')->each(function(\$cat) { echo \$cat . PHP_EOL; });"
```

## 🐛 Solución de Problemas

### Problema: "No hay productos disponibles"
**Solución:**
```bash
php artisan db:seed --class=PrendaSeeder --force
php artisan cache:clear
```

### Problema: "Las imágenes no cargan"
**Verificar:**
1. Que existe la carpeta `public/images/editados/`
2. Que los archivos de imagen están presentes
3. Ejecutar: `php artisan storage:link`

### Problema: "Error de base de datos"
**Solución:**
```bash
# Verificar configuración de BD en .env
php artisan migrate:fresh
php artisan db:seed --class=PrendaSeeder
```

## 📊 Estructura de Productos

El seeder crea:

### Categoría Formal (5 productos):
- Traje Ejecutivo Clásico - Bs. 350
- Vestido de Noche Elegante - Bs. 280  
- Blazer Moderno Profesional - Bs. 220
- Conjunto Ejecutivo Premium - Bs. 320
- Traje de Gala Exclusivo - Bs. 420

### Categoría Informal (4 productos):
- Casual Chic Urbano - Bs. 180
- Street Style Moderno - Bs. 150
- Boho Casual Libre - Bs. 160
- Weekend Vibes Relajado - Bs. 140

## 🔄 Actualizar Productos

Para agregar más productos:
1. Editar `database/seeders/PrendaSeeder.php`
2. Ejecutar: `php artisan db:seed --class=PrendaSeeder`

## 📞 Soporte

Si sigues teniendo problemas:
1. Verificar que el archivo `.env` esté configurado correctamente
2. Asegurar que la base de datos esté funcionando
3. Revisar los logs en `storage/logs/laravel.log`

---

**¡Importante!** Los productos ahora se cargan desde la **base de datos**, no desde archivos hardcodeados. Esto asegura que funcione en cualquier instalación del proyecto.