@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Saludo Simple -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    ¡Hola {{ Auth::user()->nombre }}! 👋
                </h1>
                <p class="text-gray-600">¿Qué te gustaría hacer hoy?</p>
            </div>

            <!-- Botón Principal Grande -->
            <div class="mb-8">
                <a href="{{ route('pedidos.cliente-crear') }}" 
                   class="block w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white text-center py-6 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <div class="flex flex-col items-center">
                        <div class="text-4xl mb-2">🛍️</div>
                        <h2 class="text-2xl font-bold mb-1">Hacer un Pedido</h2>
                        <p class="text-blue-100">Crea tu pedido personalizado</p>
                    </div>
                </a>
            </div>

            <!-- Acciones Rápidas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <a href="{{ route('pedidos.mis-pedidos') }}" 
                   class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="text-3xl mr-4">📋</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Mis Pedidos</h3>
                            <p class="text-gray-600 text-sm">{{ $estadisticas['total_pedidos'] }} pedidos realizados</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('catalogo.index') }}" 
                   class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="text-3xl mr-4">👕</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Ver Catálogo</h3>
                            <p class="text-gray-600 text-sm">Explora nuestros productos</p>
                        </div>
                    </div>
                </a>


            </div>

            <!-- Pedidos Recientes -->
            @if($pedidos_recientes->count() > 0)
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Mis Últimos Pedidos</h3>
                    <a href="{{ route('pedidos.mis-pedidos') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Ver todos →
                    </a>
                </div>
                <div class="space-y-4">
                    @foreach($pedidos_recientes->take(3) as $pedido)
                        <div class="p-4 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
                            <!-- Header del pedido -->
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="font-bold text-gray-800 text-lg">Pedido #{{ $pedido->id_pedido }}</p>
                                    <p class="text-sm text-gray-500">{{ $pedido->created_at->format('d/m/Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-800">
                                        Bs. {{ number_format($pedido->total ?? 0, 0) }}
                                    </p>
                                    <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                                       class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        Ver detalles →
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Barra de progreso -->
                            <x-pedido-progress :estado="$pedido->estado" />
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Mensaje de Ayuda -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center">
                <div class="text-4xl mb-3">💡</div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">¿Necesitas ayuda?</h3>
                <p class="text-gray-600 mb-4">Si tienes alguna pregunta sobre tu pedido o nuestros productos, no dudes en contactarnos.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="tel:+59112345678" class="inline-flex items-center justify-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        <span class="mr-2">📞</span>
                        Llamar
                    </a>
                    <a href="https://wa.me/59112345678" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <span class="mr-2">💬</span>
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
