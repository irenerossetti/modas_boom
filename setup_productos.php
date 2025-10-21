<?php

/**
 * Script de configuración de productos para Modas Boom
 * 
 * Este script debe ejecutarse después de clonar el proyecto
 * para asegurar que los productos estén disponibles en la base de datos.
 */

echo "🚀 Configurando productos para Modas Boom...\n\n";

// Verificar si estamos en el directorio correcto
if (!file_exists('artisan')) {
    echo "❌ Error: Este script debe ejecutarse desde la raíz del proyecto Laravel.\n";
    exit(1);
}

// Ejecutar migraciones
echo "📋 Ejecutando migraciones...\n";
$output = shell_exec('php artisan migrate --force 2>&1');
echo $output . "\n";

// Ejecutar seeder de prendas
echo "👕 Creando productos en la base de datos...\n";
$output = shell_exec('php artisan db:seed --class=PrendaSeeder 2>&1');
echo $output . "\n";

// Limpiar cache
echo "🧹 Limpiando cache...\n";
$output = shell_exec('php artisan cache:clear 2>&1');
echo $output . "\n";

// Verificar productos creados
$output = shell_exec('php artisan tinker --execute="echo \'Productos en BD: \' . App\\Models\\Prenda::count();" 2>&1');
echo $output . "\n";

echo "✅ ¡Configuración completada!\n";
echo "📱 Ahora puedes acceder al catálogo y crear pedidos.\n";
echo "🌐 Los productos se cargan desde la base de datos, no desde archivos locales.\n\n";

echo "📝 Comandos útiles:\n";
echo "   - Ver productos: php artisan tinker --execute=\"App\\Models\\Prenda::all()->pluck('nombre')\"\n";
echo "   - Recrear productos: php artisan db:seed --class=PrendaSeeder\n";
echo "   - Limpiar cache: php artisan cache:clear\n";