@extends('layouts.app')

@section('content')
    <div class="py-4 lg:py-12">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl rounded-lg">
                <div class="p-3 sm:p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4 lg:mb-6 space-y-3 lg:space-y-0">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-boom-text-dark">
                                <i class="fas fa-history mr-2"></i>
                                <span class="hidden sm:inline">Historial del Pedido #{{ $pedido->id_pedido }}</span>
                                <span class="sm:hidden">Historial #{{ $pedido->id_pedido }}</span>
                            </h2>
                            <p class="text-boom-text-medium mt-1 text-sm sm:text-base">
                                Cliente: {{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                            <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                               class="bg-white hover:bg-gray-50 text-boom-text-dark border-2 border-boom-cream-500 hover:border-boom-cream-600 font-semibold py-2 px-3 sm:px-4 rounded-lg transition-colors duration-300 text-sm sm:text-base text-center shadow-sm hover:shadow-md">
                                <i class="fas fa-eye mr-1 sm:mr-2"></i>
                                <span class="hidden sm:inline">Ver Pedido</span>
                                <span class="sm:hidden">Ver</span>
                            </a>
                            <a href="{{ route('pedidos.index') }}" 
                               class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-3 sm:px-4 rounded-lg transition-colors duration-300 text-sm sm:text-base text-center">
                                <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>
                                <span class="hidden sm:inline">Volver a Pedidos</span>
                                <span class="sm:hidden">Volver</span>
                            </a>
                        </div>
                    </div>

                    <!-- Información del Pedido -->
                    <div class="bg-boom-cream-100 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm font-medium text-boom-text-dark">Estado Actual</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-1
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
                            <div>
                                <p class="text-sm font-medium text-boom-text-dark">Total</p>
                                <p class="text-lg font-bold text-boom-rose-dark mt-1">
                                    @if($pedido->total)
                                        Bs. {{ number_format($pedido->total, 2) }}
                                    @else
                                        Por definir
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-boom-text-dark">Fecha de Creación</p>
                                <p class="text-sm text-boom-text-medium mt-1">
                                    {{ $pedido->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Debug (temporal) -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-yellow-800 mb-2">Información de Debug</h4>
                        <p class="text-yellow-700 text-sm">
                            <strong>ID del pedido:</strong> {{ $pedido->id_pedido }}<br>
                            <strong>Registros encontrados:</strong> {{ is_countable($historial) ? count($historial) : 'No es contable' }}<br>
                            <strong>Tipo de historial:</strong> {{ get_class($historial) }}
                        </p>
                    </div>

                    <!-- Historial de Cambios -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                            <i class="fas fa-clock mr-2"></i>
                            Historial de Cambios
                        </h3>

                        @if(is_countable($historial) && count($historial) > 0)
                            <div class="space-y-4">
                                @foreach($historial as $registro)
                                    <div class="border border-boom-cream-300 rounded-lg p-4 hover:bg-boom-cream-50 transition-colors duration-300">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium mr-3
                                                        @if($registro->accion == 'CREATE') bg-green-100 text-green-800
                                                        @elseif($registro->accion == 'UPDATE') bg-blue-100 text-blue-800
                                                        @elseif($registro->accion == 'DELETE') bg-red-100 text-red-800
                                                        @elseif($registro->accion == 'VIEW') bg-gray-100 text-gray-800
                                                        @else bg-purple-100 text-purple-800 @endif">
                                                        @if($registro->accion == 'CREATE')
                                                            <i class="fas fa-plus mr-1"></i>CREADO
                                                        @elseif($registro->accion == 'UPDATE')
                                                            <i class="fas fa-edit mr-1"></i>MODIFICADO
                                                        @elseif($registro->accion == 'DELETE')
                                                            <i class="fas fa-trash mr-1"></i>ELIMINADO
                                                        @elseif($registro->accion == 'VIEW')
                                                            <i class="fas fa-eye mr-1"></i>CONSULTADO
                                                        @else
                                                            <i class="fas fa-cog mr-1"></i>{{ $registro->accion }}
                                                        @endif
                                                    </span>
                                                    <span class="text-sm font-medium text-boom-text-dark">
                                                        {{ $registro->usuario->nombre ?? 'Sistema' }}
                                                    </span>
                                                </div>
                                                
                                                <p class="text-boom-text-dark mb-2">{{ $registro->descripcion }}</p>
                                                
                                                @if($registro->datos_nuevos)
                                                    @php
                                                        $datosNuevos = is_string($registro->datos_nuevos) 
                                                            ? json_decode($registro->datos_nuevos, true) 
                                                            : $registro->datos_nuevos;
                                                    @endphp
                                                    @if(is_array($datosNuevos))
                                                    <div class="bg-green-50 border border-green-200 rounded p-3 mt-2">
                                                        <p class="text-xs font-medium text-green-800 mb-1">Datos registrados:</p>
                                                        <div class="text-xs text-green-700">
                                                            @if(isset($datosNuevos['producto']))
                                                                <p><strong>Producto:</strong> {{ $datosNuevos['producto'] }}</p>
                                                            @endif
                                                            @if(isset($datosNuevos['categoria']))
                                                                <p><strong>Categoría:</strong> {{ $datosNuevos['categoria'] }}</p>
                                                            @endif
                                                            @if(isset($datosNuevos['cantidad_docenas']))
                                                                <p><strong>Cantidad:</strong> {{ $datosNuevos['cantidad_docenas'] }} docena(s) ({{ $datosNuevos['cantidad_unidades'] ?? ($datosNuevos['cantidad_docenas'] * 12) }} unidades)</p>
                                                            @endif
                                                            @if(isset($datosNuevos['precio_por_docena']))
                                                                <p><strong>Precio por docena:</strong> Bs. {{ number_format($datosNuevos['precio_por_docena'], 2) }}</p>
                                                            @endif
                                                            @if(isset($datosNuevos['total']))
                                                                <p><strong>Total:</strong> Bs. {{ number_format($datosNuevos['total'], 2) }}</p>
                                                            @endif
                                                            @if(isset($datosNuevos['estado']))
                                                                <p><strong>Estado:</strong> {{ $datosNuevos['estado'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endif

                                                @if($registro->datos_anteriores)
                                                    @php
                                                        $datosAnteriores = is_string($registro->datos_anteriores) 
                                                            ? json_decode($registro->datos_anteriores, true) 
                                                            : $registro->datos_anteriores;
                                                    @endphp
                                                    @if(is_array($datosAnteriores))
                                                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mt-2">
                                                        <p class="text-xs font-medium text-yellow-800 mb-1">Datos anteriores:</p>
                                                        <div class="text-xs text-yellow-700">
                                                            @if(isset($datosAnteriores['estado']))
                                                                <p><strong>Estado anterior:</strong> {{ $datosAnteriores['estado'] }}</p>
                                                            @endif
                                                            @if(isset($datosAnteriores['total']))
                                                                <p><strong>Total anterior:</strong> Bs. {{ number_format($datosAnteriores['total'], 2) }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endif
                                            </div>
                                            
                                            <div class="text-right text-sm text-boom-text-medium ml-4">
                                                <p>{{ $registro->created_at->format('d/m/Y') }}</p>
                                                <p>{{ $registro->created_at->format('H:i:s') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Paginación si es necesaria -->
                            @if(method_exists($historial, 'hasPages') && $historial->hasPages())
                                <div class="mt-6">
                                    {{ $historial->links() }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-12">
                                <div class="bg-boom-cream-100 rounded-lg p-8">
                                    <i class="fas fa-history text-6xl text-boom-text-medium mb-4"></i>
                                    <h3 class="text-xl font-semibold text-boom-text-dark mb-2">Sin historial disponible</h3>
                                    <p class="text-boom-text-medium">
                                        No se encontraron registros de cambios para este pedido en la bitácora del sistema.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-blue-800 mb-1">Información sobre el historial</h4>
                                <p class="text-blue-700 text-sm">
                                    Este historial muestra todos los cambios y acciones realizadas sobre el pedido, 
                                    incluyendo creación, modificaciones, consultas y cambios de estado. 
                                    Los datos se registran automáticamente en la bitácora del sistema.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
