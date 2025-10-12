<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class OptimizeApp extends Command
{
    protected $signature = 'app:optimize';
    protected $description = 'Optimiza la aplicación limpiando y cacheando configuraciones';

    public function handle()
    {
        $this->info('🚀 Iniciando optimización de la aplicación...');

        // Limpiar caches existentes
        $this->info('🧹 Limpiando caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // Cachear configuraciones
        $this->info('⚡ Cacheando configuraciones...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        // Limpiar caches específicos de la aplicación
        $this->info('🗑️ Limpiando caches de aplicación...');
        Cache::forget('productos_catalogo');
        Cache::forget('clientes_con_pedidos');
        Cache::forget('clientes_activos');
        Cache::forget('operarios_activos');

        $this->info('✅ Optimización completada exitosamente!');
        
        return 0;
    }
}