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
                        <button onclick="mostrarModalAvance()" 
                                class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Nuevo Avance
                        </button>
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
                        <button onclick="mostrarModalAvance()" 
                                class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Registrar Primer Avance
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Botón flotante para agregar avance -->
@if(in_array($pedido->estado, ['Asignado', 'En producción']) && in_array(Auth::user()->id_rol, [1, 2]))
<div class="fixed bottom-6 right-6 z-40">
    <button onclick="mostrarModalAvance()" 
            class="inline-flex items-center justify-center w-16 h-16 bg-orange-500 hover:bg-orange-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110"
            title="Registrar Avance">
        <i class="fas fa-plus text-xl"></i>
    </button>
</div>
@endif

<!-- Modal Registrar Avance de Producción -->
@if(in_array($pedido->estado, ['Asignado', 'En producción']) && in_array(Auth::user()->id_rol, [1, 2]))
<div id="modalAvance" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">CU20: Registrar Avance de Producción</h3>
                <button onclick="cerrarModalAvance(event)" class="text-gray-400 hover:text-gray-600 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <form id="formAvance" action="{{ route('pedidos.procesar-avance', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    
                    <!-- Artículo -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Artículo</label>
                        <select name="prenda_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                            @foreach($pedido->prendas as $prenda)
                                <option value="{{ $prenda->id }}">{{ $prenda->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Etapa -->
                    <div class="mb-6">
                        <label for="etapa_modal" class="block text-sm font-medium text-gray-700 mb-2">Etapa</label>
                        <select id="etapa_modal" name="etapa" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                            <option value="">Seleccionar etapa...</option>
                            <option value="Corte">Corte</option>
                            <option value="Confección">Confección</option>
                            <option value="Acabado">Acabado</option>
                            <option value="Control de Calidad">Control de Calidad</option>
                        </select>
                    </div>
                    
                    <!-- Porcentaje de Avance -->
                    <div class="mb-6">
                        <label for="porcentaje_avance_modal" class="block text-sm font-medium text-gray-700 mb-3">
                            Porcentaje de Avance: <span id="porcentaje_display" class="font-semibold">0%</span>
                        </label>
                        <div class="relative">
                            <input type="range" 
                                   id="porcentaje_avance_modal" 
                                   name="porcentaje_avance" 
                                   min="0" max="100" value="0" 
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-thumb"
                                   oninput="document.getElementById('porcentaje_display').textContent = this.value + '%'">
                        </div>
                    </div>
                    
                    <!-- Notas -->
                    <div class="mb-8">
                        <label for="descripcion_modal" class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                        <textarea id="descripcion_modal" 
                                  name="descripcion" 
                                  rows="4" 
                                  required
                                  placeholder="Descripción del avance..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                    </div>
                    
                    <!-- Botones -->
                    <div class="flex justify-center space-x-4">
                        <button type="submit" 
                                class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg font-medium transition-colors duration-200">
                            Confirmar
                        </button>
                        <button type="button" 
                                onclick="cerrarModalAvance(event)" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-8 py-3 rounded-lg font-medium transition-colors duration-200">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
    /* Estilos para el slider personalizado */
    .slider-thumb::-webkit-slider-thumb {
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #1f2937;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .slider-thumb::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #1f2937;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .slider-thumb::-webkit-slider-track {
        height: 8px;
        border-radius: 4px;
        background: #e5e7eb;
    }
    
    .slider-thumb::-moz-range-track {
        height: 8px;
        border-radius: 4px;
        background: #e5e7eb;
        border: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // ========== MODAL REGISTRAR AVANCE ==========
    function mostrarModalAvance() {
        document.getElementById('modalAvance').classList.remove('hidden');
    }

    function cerrarModalAvance(event) {
        if (event) event.preventDefault();
        document.getElementById('modalAvance').classList.add('hidden');
        document.getElementById('formAvance').reset();
        document.getElementById('porcentaje_display').textContent = '0%';
    }

    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalAvance();
        }
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('modalAvance')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalAvance();
        }
    });
</script>
@endpush

@endsection