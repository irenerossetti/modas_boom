@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-purple-600 to-blue-600 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold">🖥️ Panel del Sistema</h1>
                            <p class="text-purple-100 mt-1">Monitoreo y diagnóstico completo - Modas Boom</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold" id="current-time">{{ now()->format('H:i:s') }}</div>
                            <div class="text-sm text-purple-100">{{ now()->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Usuarios -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-blue-50">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-blue-900">{{ $stats['usuarios'] }}</div>
                                <div class="text-sm text-blue-600">Usuarios Totales</div>
                                <div class="text-xs text-blue-500">{{ $stats['usuarios_activos'] }} activos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pedidos -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-green-50">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-shopping-cart text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-green-900">{{ $stats['pedidos'] }}</div>
                                <div class="text-sm text-green-600">Pedidos Totales</div>
                                <div class="text-xs text-green-500">{{ $stats['pedidos_activos'] }} activos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prendas -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-purple-50">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-tshirt text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-purple-900">{{ $stats['prendas'] }}</div>
                                <div class="text-sm text-purple-600">Prendas Totales</div>
                                <div class="text-xs text-purple-500">{{ $stats['prendas_activas'] }} activas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-orange-50">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-boxes text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-orange-900">{{ $stats['stock_total'] }}</div>
                                <div class="text-sm text-orange-600">Stock Total</div>
                                <div class="text-xs text-orange-500">{{ $stats['clientes'] }} clientes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos y Diagramas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Usuarios por Rol -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-user-tag mr-2 text-blue-500"></i>Usuarios por Rol
                        </h3>
                        <div class="space-y-3">
                            @foreach($usuariosPorRol as $rol)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">{{ $rol['rol'] }}</span>
                                    <div class="flex items-center">
                                        <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($rol['total'] / $stats['usuarios']) * 100 }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $rol['total'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Pedidos por Estado -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-chart-pie mr-2 text-green-500"></i>Pedidos por Estado
                        </h3>
                        <div class="space-y-3">
                            @foreach($pedidosPorEstado as $estado)
                                @php
                                    $colores = [
                                        'En proceso' => 'bg-blue-500',
                                        'Asignado' => 'bg-yellow-500',
                                        'En producción' => 'bg-orange-500',
                                        'Terminado' => 'bg-green-500',
                                        'Entregado' => 'bg-purple-500',
                                        'Cancelado' => 'bg-red-500'
                                    ];
                                    $color = $colores[$estado->estado] ?? 'bg-gray-500';
                                @endphp
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">{{ $estado->estado }}</span>
                                    <div class="flex items-center">
                                        <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                            <div class="{{ $color }} h-2 rounded-full" style="width: {{ ($estado->total / $stats['pedidos']) * 100 }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $estado->total }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Sistema y Performance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Información del Sistema -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-server mr-2 text-purple-500"></i>Información del Sistema
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Laravel:</span>
                                <span class="text-sm font-medium">{{ $sistemaInfo['version_laravel'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">PHP:</span>
                                <span class="text-sm font-medium">{{ $sistemaInfo['version_php'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Base de Datos:</span>
                                <span class="text-sm font-medium">{{ $sistemaInfo['base_datos'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Cache:</span>
                                <span class="text-sm font-medium">{{ $sistemaInfo['cache_driver'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Zona Horaria:</span>
                                <span class="text-sm font-medium">{{ $sistemaInfo['timezone'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Debug Mode:</span>
                                <span class="text-sm font-medium {{ $sistemaInfo['debug_mode'] ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $sistemaInfo['debug_mode'] ? 'Habilitado' : 'Deshabilitado' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-tachometer-alt mr-2 text-orange-500"></i>Performance
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Tiempo de Respuesta:</span>
                                <span class="text-sm font-medium text-green-600">{{ $performance['response_time'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Uso de Memoria:</span>
                                <span class="text-sm font-medium">{{ $performance['memory_usage'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Cache Hits:</span>
                                <span class="text-sm font-medium">{{ $performance['cache_hits'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Estado:</span>
                                <span class="text-sm font-medium text-green-600">
                                    <i class="fas fa-circle text-green-500 mr-1"></i>Online
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actividad Reciente y Diagrama -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Actividad Reciente -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-history mr-2 text-blue-500"></i>Actividad Reciente
                        </h3>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            @forelse($actividadReciente as $pedido)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-shopping-cart text-white text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium">Pedido #{{ $pedido->id_pedido }}</div>
                                            <div class="text-xs text-gray-500">{{ $pedido->cliente->nombre ?? 'Cliente' }} - {{ $pedido->estado }}</div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $pedido->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>No hay actividad reciente</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Acceso Rápido al Diagrama -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-sitemap mr-2 text-purple-500"></i>Arquitectura del Sistema
                        </h3>
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="fas fa-project-diagram text-6xl text-purple-500"></i>
                            </div>
                            <p class="text-gray-600 mb-4">Ver el diagrama completo de comunicación y arquitectura del sistema</p>
                            <a href="{{ route('sistema.diagrama') }}" 
                               class="bg-purple-500 hover:bg-purple-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-300">
                                <i class="fas fa-eye mr-2"></i>Ver Diagrama Completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript para actualización en tiempo real -->
    <script>
        // Actualizar hora cada segundo
        setInterval(function() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString();
        }, 1000);

        // Actualizar estadísticas cada 30 segundos
        setInterval(function() {
            fetch('{{ route("sistema.estadisticas") }}')
                .then(response => response.json())
                .then(data => {
                    console.log('Estadísticas actualizadas:', data);
                    // Aquí se pueden actualizar elementos específicos
                })
                .catch(error => console.log('Error:', error));
        }, 30000);
    </script>
@endsection
