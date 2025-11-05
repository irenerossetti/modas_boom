@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-history text-purple-500 mr-2"></i>
                        Historial de Avances de Producción
                    </h1>
                    <p class="text-gray-600 mt-1">Pedido #{{ $pedido->id_pedido }} - {{ $pedido->cliente->nombre }}</p>
                </div>
                <div class="flex space-x-3">
                    @if(in_array($pedido->estado, ['Asignado', 'En producción']) && in_array(Auth::user()->id_rol, [1, 2]))
                        <a href="{{ route('pedidos.registrar-avance', $pedido->id_pedido) }}" 
                           class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Nuevo Avance
                        </a>
                    @endif
                    <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Información del Pedido -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Información del Pedido
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $pedido->id_pedido }}</div>
                    <div class="text-sm text-gray-600">Número de Pedido</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-lg font-semibold 
                        @if($pedido->estado == 'Pendiente') text-yellow-600
                        @elseif($pedido->estado == 'En proceso') text-blue-600
                        @elseif($pedido->estado == 'Asignado') text-purple-600
                        @elseif($pedido->estado == 'En producción') text-orange-600
                        @elseif($pedido->estado == 'Terminado') text-green-600
                        @elseif($pedido->estado == 'Entregado') text-gray-600
                        @else text-red-600 @endif">
                        {{ $pedido->estado }}
                    </div>
                    <div class="text-sm text-gray-600">Estado</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-lg font-semibold text-gray-900">${{ number_format($pedido->total, 2) }}</div>
                    <div class="text-sm text-gray-600">Total</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-lg font-semibold text-gray-900">{{ $avances->count() }}</div>
                    <div class="text-sm text-gray-600">Avances Registrados</div>
                </div>
            </div>
        </div>

        <!-- Progreso General -->
        @if($avances->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-line text-green-500 mr-2"></i>Progreso por Etapas
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                    $etapas = ['Corte', 'Confección', 'Acabado', 'Control de Calidad'];
                    $colores = ['bg-red-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
                @endphp
                
                @foreach($etapas as $index => $etapa)
                    @php
                        $avanceEtapa = $avances->where('etapa', $etapa)->sortByDesc('created_at')->first();
                        $porcentaje = $avanceEtapa ? $avanceEtapa->porcentaje_avance : 0;
                    @endphp
                    
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold {{ $porcentaje > 0 ? 'text-' . str_replace('bg-', '', $colores[$index]) : 'text-gray-400' }}">
                            {{ $porcentaje }}%
                        </div>
                        <div class="text-sm text-gray-600 mb-2">{{ $etapa }}</div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="{{ $colores[$index] }} h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $porcentaje }}%"></div>
                        </div>
                        @if($avanceEtapa)
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $avanceEtapa->created_at->format('d/m/Y') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Lista de Avances -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list text-orange-500 mr-2"></i>Registro de Avances
                </h2>
            </div>
            
            @if($avances->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($avances as $avance)
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        @if($avance->etapa == 'Corte') bg-red-100 text-red-800
                                        @elseif($avance->etapa == 'Confección') bg-yellow-100 text-yellow-800
                                        @elseif($avance->etapa == 'Acabado') bg-blue-100 text-blue-800
                                        @elseif($avance->etapa == 'Control de Calidad') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $avance->etapa }}
                                    </span>
                                    <span class="ml-3 text-2xl font-bold text-orange-600">
                                        {{ $avance->porcentaje_avance }}%
                                    </span>
                                </div>
                                
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $avance->descripcion }}</h3>
                                
                                @if($avance->observaciones)
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-3">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-700">
                                                    <strong>Observaciones:</strong> {{ $avance->observaciones }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-user mr-1"></i>
                                    <span>{{ $avance->registradoPor->nombre ?? 'Usuario no encontrado' }}</span>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock mr-1"></i>
                                    <span>{{ $avance->created_at->format('d/m/Y H:i') }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ $avance->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            <div class="ml-4 flex-shrink-0">
                                <div class="w-16 h-16 rounded-full border-4 border-gray-200 flex items-center justify-center
                                    @if($avance->porcentaje_avance >= 100) border-green-500 bg-green-50
                                    @elseif($avance->porcentaje_avance >= 75) border-blue-500 bg-blue-50
                                    @elseif($avance->porcentaje_avance >= 50) border-yellow-500 bg-yellow-50
                                    @else border-red-500 bg-red-50 @endif">
                                    <span class="text-lg font-bold
                                        @if($avance->porcentaje_avance >= 100) text-green-600
                                        @elseif($avance->porcentaje_avance >= 75) text-blue-600
                                        @elseif($avance->porcentaje_avance >= 50) text-yellow-600
                                        @else text-red-600 @endif">
                                        {{ $avance->porcentaje_avance }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay avances registrados</h3>
                    <p class="text-gray-500 mb-6">Este pedido aún no tiene avances de producción registrados.</p>
                    
                    @if(in_array($pedido->estado, ['Asignado', 'En producción']) && in_array(Auth::user()->id_rol, [1, 2]))
                        <a href="{{ route('pedidos.registrar-avance', $pedido->id_pedido) }}" 
                           class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Registrar Primer Avance
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection