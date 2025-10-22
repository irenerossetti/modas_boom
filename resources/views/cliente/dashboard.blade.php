<x-app-layout>
    <div class="p-2 sm:p-4 lg:p-6">
        <!-- Banner de acción principal -->
        @if($estadisticas['total_pedidos'] == 0)
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 lg:p-6 mb-6 text-white">
                <div class="text-center">
                    <h1 class="text-2xl lg:text-3xl font-bold mb-2">
                        🎉 ¡Bienvenido {{ Auth::user()->nombre }}!
                    </h1>
                    <p class="text-lg opacity-90 mb-4">¡Es hora de hacer tu primer pedido personalizado!</p>
                    <a href="{{ route('pedidos.cliente-crear') }}" 
                       class="inline-flex items-center px-8 py-4 bg-white text-green-600 font-bold rounded-lg hover:bg-gray-100 transition-colors duration-300 shadow-lg text-lg">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        🛍️ Hacer Mi Primer Pedido
                    </a>
                </div>
            </div>
        @else
            <!-- Header de bienvenida -->
            <div class="bg-gradient-to-r from-boom-rose-dark to-boom-rose-light rounded-lg p-4 lg:p-6 mb-6 text-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold mb-2">
                            ¡Hola {{ Auth::user()->nombre }}! 👋
                        </h1>
                        <p class="text-lg opacity-90">Bienvenido a tu panel personal de Modas Boom</p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <a href="{{ route('pedidos.cliente-crear') }}" 
                           class="inline-flex items-center px-6 py-3 bg-white text-boom-rose-dark font-bold rounded-lg hover:bg-gray-100 transition-colors duration-300 shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Hacer Nuevo Pedido
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Estadísticas -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-boom-cream-300">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500">Total Pedidos</p>
                        <p class="text-xl font-bold text-boom-text-dark">{{ $estadisticas['total_pedidos'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border border-boom-cream-300">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500">En Proceso</p>
                        <p class="text-xl font-bold text-boom-text-dark">{{ $estadisticas['pedidos_activos'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border border-boom-cream-300">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500">Completados</p>
                        <p class="text-xl font-bold text-boom-text-dark">{{ $estadisticas['pedidos_completados'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border border-boom-cream-300">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500">Total Gastado</p>
                        <p class="text-xl font-bold text-boom-text-dark">Bs. {{ number_format($estadisticas['total_gastado'], 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pedidos Recientes -->
            <div class="bg-white rounded-lg shadow-sm border border-boom-cream-300">
                <div class="p-4 border-b border-boom-cream-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-boom-text-dark">Mis Pedidos Recientes</h3>
                        <a href="{{ route('pedidos.mis-pedidos') }}" class="text-boom-rose-dark hover:text-boom-rose-light text-sm font-medium">
                            Ver todos →
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    @if($pedidos_recientes->count() > 0)
                        <div class="space-y-3">
                            @foreach($pedidos_recientes as $pedido)
                                <div class="flex items-center justify-between p-3 bg-boom-cream-100 rounded-lg">
                                    <div>
                                        <p class="font-semibold text-boom-text-dark">Pedido #{{ $pedido->id_pedido }}</p>
                                        <p class="text-sm text-gray-500">{{ $pedido->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($pedido->estado == 'En proceso') bg-yellow-100 text-yellow-800
                                            @elseif($pedido->estado == 'Asignado') bg-blue-100 text-blue-800
                                            @elseif($pedido->estado == 'En producción') bg-purple-100 text-purple-800
                                            @elseif($pedido->estado == 'Terminado') bg-green-100 text-green-800
                                            @elseif($pedido->estado == 'Entregado') bg-green-200 text-green-900
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $pedido->estado }}
                                        </span>
                                        <p class="text-sm font-semibold text-boom-text-dark mt-1">
                                            Bs. {{ number_format($pedido->total ?? 0, 0) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <p class="text-gray-500 mb-4">Aún no tienes pedidos</p>
                            <a href="{{ route('pedidos.cliente-crear') }}" class="inline-flex items-center px-4 py-2 bg-boom-rose-dark text-white rounded-lg hover:bg-boom-rose-light transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Hacer Mi Primer Pedido
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Productos Populares -->
            <div class="bg-white rounded-lg shadow-sm border border-boom-cream-300">
                <div class="p-4 border-b border-boom-cream-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-boom-text-dark">Productos Populares</h3>
                        <a href="{{ route('catalogo.index') }}" class="text-boom-rose-dark hover:text-boom-rose-light text-sm font-medium">
                            Ver catálogo →
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($productos_populares->take(4) as $producto)
                            <div class="bg-boom-cream-100 rounded-lg p-3 hover:bg-boom-cream-200 transition-colors cursor-pointer"
                                 onclick="window.location.href='{{ route('catalogo.index') }}'">
                                <div class="text-center">
                                    @if($producto->imagen && file_exists(public_path($producto->imagen)))
                                        <img src="{{ asset($producto->imagen) }}" 
                                             alt="{{ $producto->nombre }}" 
                                             class="w-16 h-16 object-cover rounded-lg mx-auto mb-2">
                                    @else
                                        <div class="w-16 h-16 bg-boom-cream-300 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                            <i class="fas fa-tshirt text-boom-text-medium"></i>
                                        </div>
                                    @endif
                                    <p class="text-xs font-semibold text-boom-text-dark">{{ Str::limit($producto->nombre, 20) }}</p>
                                    <p class="text-xs text-boom-rose-dark font-bold">Bs. {{ number_format($producto->precio, 0) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('pedidos.cliente-crear') }}" 
                           class="inline-flex items-center px-4 py-2 bg-boom-rose-dark text-white rounded-lg hover:bg-boom-rose-light transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Crear Pedido Personalizado
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-boom-cream-300 p-4">
            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">Acciones Rápidas</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('pedidos.cliente-crear') }}" 
                   class="flex items-center p-4 bg-boom-rose-dark text-white rounded-lg hover:bg-boom-rose-light transition-colors">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <div>
                        <p class="font-semibold">Hacer Pedido</p>
                        <p class="text-sm opacity-90">Crea un nuevo pedido personalizado</p>
                    </div>
                </a>

                <a href="{{ route('catalogo.index') }}" 
                   class="flex items-center p-4 bg-boom-cream-300 text-boom-text-dark rounded-lg hover:bg-boom-cream-400 transition-colors">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <div>
                        <p class="font-semibold">Ver Catálogo</p>
                        <p class="text-sm">Explora nuestros productos</p>
                    </div>
                </a>

                <a href="{{ route('pedidos.mis-pedidos') }}" 
                   class="flex items-center p-4 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition-colors">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <div>
                        <p class="font-semibold">Mis Pedidos</p>
                        <p class="text-sm">Revisa el estado de tus pedidos</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>