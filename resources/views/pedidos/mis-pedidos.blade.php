@extends('layouts.app')

@section('content')
    <div class="py-4 lg:py-12">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl rounded-lg">
                <div class="p-3 sm:p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 lg:mb-6 space-y-3 sm:space-y-0">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-boom-text-dark">Mis Pedidos</h2>
                            @if(Auth::user()->id_rol == 2)
                                <p class="text-sm text-boom-text-medium mt-1">Pedidos personales como empleado</p>
                            @endif
                        </div>
                        <a href="{{ route('pedidos.cliente-crear') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-3 sm:px-4 rounded-lg transition-colors duration-300 text-sm sm:text-base text-center">
                            <i class="fas fa-plus mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Hacer Nuevo Pedido</span>
                            <span class="sm:hidden">Nuevo Pedido</span>
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(!$cliente)
                        <div class="text-center py-12">
                            <div class="bg-boom-cream-100 rounded-lg p-8">
                                <i class="fas fa-shopping-bag text-6xl text-boom-text-medium mb-4"></i>
                                <h3 class="text-xl font-semibold text-boom-text-dark mb-2">¡Bienvenido!</h3>
                                <p class="text-boom-text-medium mb-6">Aún no tienes pedidos registrados. ¡Haz tu primer pedido ahora!</p>
                                <a href="{{ route('pedidos.cliente-crear') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-300">
                                    <i class="fas fa-plus mr-2"></i>Hacer Mi Primer Pedido
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Filtros -->
                        <div class="bg-boom-cream-100 rounded-lg p-3 sm:p-4 mb-4 lg:mb-6">
                            <form method="GET" action="{{ route('pedidos.mis-pedidos') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                                <div>
                                    <label for="estado" class="block text-sm font-medium text-boom-text-dark mb-1">Estado</label>
                                    <select name="estado" id="estado" class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark">
                                        <option value="">Todos los estados</option>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado }}" {{ (request('estado') == $estado) ? 'selected' : '' }}>
                                                {{ $estado }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="fecha_desde" class="block text-sm font-medium text-boom-text-dark mb-1">Desde</label>
                                    <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" 
                                           class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark">
                                </div>
                                <div>
                                    <label for="fecha_hasta" class="block text-sm font-medium text-boom-text-dark mb-1">Hasta</label>
                                    <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" 
                                           class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark">
                                </div>
                                <div class="flex items-end space-x-2">
                                    <button type="submit" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                        <i class="fas fa-search mr-1"></i>Filtrar
                                    </button>
                                    <a href="{{ route('pedidos.mis-pedidos') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300 shadow-sm hover:shadow-md">
                                        <i class="fas fa-times mr-1"></i>Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>

                        @if($pedidos->count() > 0)
                            <!-- Lista de Pedidos -->
                            <div class="space-y-4">
                                @foreach($pedidos as $pedido)
                                    <div class="border border-boom-cream-300 rounded-lg p-6 hover:shadow-md transition-shadow duration-300">
                                        <div class="flex items-center justify-between mb-4">
                                            <div>
                                                <h3 class="text-lg font-semibold text-boom-text-dark">
                                                    Pedido #{{ $pedido->id_pedido }}
                                                </h3>
                                                <p class="text-sm text-boom-text-medium">
                                                    Creado el {{ $pedido->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                    @if($pedido->estado == 'En proceso') bg-yellow-100 text-yellow-800
                                                    @elseif($pedido->estado == 'Asignado') bg-blue-100 text-blue-800
                                                    @elseif($pedido->estado == 'En producción') bg-purple-100 text-purple-800
                                                    @elseif($pedido->estado == 'Terminado') bg-green-100 text-green-800
                                                    @elseif($pedido->estado == 'Entregado') bg-green-200 text-green-900
                                                    @elseif($pedido->estado == 'Cancelado') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ $pedido->estado }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                            <div>
                                                <p class="text-sm font-medium text-boom-text-dark">Total</p>
                                                <p class="text-lg font-bold text-boom-rose-dark">
                                                    @if($pedido->total)
                                                        Bs. {{ number_format($pedido->total, 2) }}
                                                    @else
                                                        Por definir
                                                    @endif
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-boom-text-dark">Estado Actual</p>
                                                <p class="text-sm text-boom-text-medium">
                                                    @if($pedido->estado == 'En proceso')
                                                        Tu pedido está siendo revisado
                                                    @elseif($pedido->estado == 'Asignado')
                                                        Asignado a un operario
                                                    @elseif($pedido->estado == 'En producción')
                                                        En proceso de confección
                                                    @elseif($pedido->estado == 'Terminado')
                                                        Listo para entrega
                                                    @elseif($pedido->estado == 'Entregado')
                                                        Pedido completado
                                                    @elseif($pedido->estado == 'Cancelado')
                                                        Pedido cancelado
                                                    @endif
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-boom-text-dark">Última Actualización</p>
                                                <p class="text-sm text-boom-text-medium">
                                                    {{ $pedido->updated_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                                               class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                                <i class="fas fa-eye mr-2"></i>Ver Detalles
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Paginación -->
                            <div class="mt-6">
                                {{ $pedidos->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="bg-boom-cream-100 rounded-lg p-8">
                                    <i class="fas fa-search text-6xl text-boom-text-medium mb-4"></i>
                                    <h3 class="text-xl font-semibold text-boom-text-dark mb-2">No se encontraron pedidos</h3>
                                    <p class="text-boom-text-medium mb-6">
                                        @if(request()->hasAny(['estado', 'fecha_desde', 'fecha_hasta']))
                                            No hay pedidos que coincidan con los filtros seleccionados.
                                        @else
                                            Aún no tienes pedidos registrados. ¡Haz tu primer pedido!
                                        @endif
                                    </p>
                                    @if(request()->hasAny(['estado', 'fecha_desde', 'fecha_hasta']))
                                        <a href="{{ route('pedidos.mis-pedidos') }}" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-semibold py-2 px-4 rounded-lg transition-colors duration-300 mr-4">
                                            <i class="fas fa-times mr-2"></i>Limpiar Filtros
                                        </a>
                                    @endif
                                    <a href="{{ route('pedidos.cliente-crear') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                        <i class="fas fa-plus mr-2"></i>Hacer Pedido
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
