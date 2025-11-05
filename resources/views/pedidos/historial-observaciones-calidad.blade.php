@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-clipboard-list text-green-600 mr-2"></i>
                        Historial de Observaciones de Calidad
                    </h1>
                    <p class="text-gray-600 mt-1">Pedido #{{ $pedido->id_pedido }} - {{ $pedido->cliente->nombre }}</p>
                </div>
                <div class="flex space-x-3">
                    @if(in_array($pedido->estado, ['En producción', 'Terminado']) && in_array(Auth::user()->id_rol, [1, 2]))
                        <a href="{{ route('pedidos.registrar-observacion-calidad', $pedido->id_pedido) }}" 
                           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Nueva Observación
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
                    <div class="text-lg font-semibold text-gray-900">{{ $observaciones->count() }}</div>
                    <div class="text-sm text-gray-600">Observaciones</div>
                </div>
            </div>
        </div>

        <!-- Resumen de Observaciones -->
        @if($observaciones->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-pie text-purple-500 mr-2"></i>Resumen de Observaciones
            </h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $tipos = $observaciones->groupBy('tipo_observacion');
                    $estados = $observaciones->groupBy('estado');
                    $prioridades = $observaciones->groupBy('prioridad');
                @endphp
                
                <!-- Por Tipo -->
                @foreach(['Defecto', 'Mejora', 'Aprobado', 'Rechazado'] as $tipo)
                    @php $count = $tipos->get($tipo, collect())->count(); @endphp
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-xl font-bold 
                            @if($tipo == 'Defecto') text-red-600
                            @elseif($tipo == 'Mejora') text-blue-600
                            @elseif($tipo == 'Aprobado') text-green-600
                            @else text-red-600 @endif">
                            {{ $count }}
                        </div>
                        <div class="text-xs text-gray-600">{{ $tipo }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Lista de Observaciones -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list text-green-500 mr-2"></i>Registro de Observaciones
                </h2>
            </div>
            
            @if($observaciones->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($observaciones as $observacion)
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-3 space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($observacion->tipo_observacion == 'Defecto') bg-red-100 text-red-800
                                        @elseif($observacion->tipo_observacion == 'Mejora') bg-blue-100 text-blue-800
                                        @elseif($observacion->tipo_observacion == 'Aprobado') bg-green-100 text-green-800
                                        @elseif($observacion->tipo_observacion == 'Rechazado') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $observacion->tipo_observacion }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($observacion->prioridad == 'Crítica') bg-red-100 text-red-800
                                        @elseif($observacion->prioridad == 'Alta') bg-orange-100 text-orange-800
                                        @elseif($observacion->prioridad == 'Media') bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ $observacion->prioridad }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($observacion->estado == 'Pendiente') bg-yellow-100 text-yellow-800
                                        @elseif($observacion->estado == 'En corrección') bg-blue-100 text-blue-800
                                        @elseif($observacion->estado == 'Corregido') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $observacion->estado }}
                                    </span>
                                </div>
                                
                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                    Área: {{ $observacion->area_afectada }}
                                </h3>
                                
                                <p class="text-gray-700 mb-3">{{ $observacion->descripcion }}</p>
                                
                                @if($observacion->accion_correctiva)
                                    <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-3">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-tools text-blue-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-blue-700">
                                                    <strong>Acción Correctiva:</strong> {{ $observacion->accion_correctiva }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="flex items-center text-sm text-gray-500 space-x-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-user mr-1"></i>
                                        <span>{{ $observacion->registradoPor->nombre ?? 'Usuario no encontrado' }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        <span>{{ $observacion->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if($observacion->fecha_correccion)
                                    <div class="flex items-center">
                                        <i class="fas fa-check mr-1"></i>
                                        <span>Corregido: {{ $observacion->fecha_correccion->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="ml-4 flex-shrink-0">
                                @if($observacion->estado !== 'Cerrado' && in_array(Auth::user()->id_rol, [1, 2]))
                                    <button onclick="mostrarModalActualizar({{ $observacion->id }}, '{{ $observacion->estado }}', '{{ $observacion->accion_correctiva }}')" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                        <i class="fas fa-edit mr-1"></i>Actualizar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <i class="fas fa-clipboard-check text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay observaciones de calidad</h3>
                    <p class="text-gray-500 mb-6">Este pedido aún no tiene observaciones de calidad registradas.</p>
                    
                    @if(in_array($pedido->estado, ['En producción', 'Terminado']) && in_array(Auth::user()->id_rol, [1, 2]))
                        <a href="{{ route('pedidos.registrar-observacion-calidad', $pedido->id_pedido) }}" 
                           class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Registrar Primera Observación
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para actualizar observación -->
<div id="modalActualizar" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Actualizar Observación</h3>
            <form id="formActualizar" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select id="estado" name="estado" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Pendiente">Pendiente</option>
                        <option value="En corrección">En corrección</option>
                        <option value="Corregido">Corregido</option>
                        <option value="Cerrado">Cerrado</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="accion_correctiva_modal" class="block text-sm font-medium text-gray-700 mb-2">Acción Correctiva</label>
                    <textarea id="accion_correctiva_modal" name="accion_correctiva" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModal()" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function mostrarModalActualizar(observacionId, estadoActual, accionCorrectiva) {
    const modal = document.getElementById('modalActualizar');
    const form = document.getElementById('formActualizar');
    const estadoSelect = document.getElementById('estado');
    const accionTextarea = document.getElementById('accion_correctiva_modal');
    
    form.action = `{{ route('pedidos.actualizar-observacion-calidad', [$pedido->id_pedido, ':id']) }}`.replace(':id', observacionId);
    estadoSelect.value = estadoActual;
    accionTextarea.value = accionCorrectiva || '';
    
    modal.classList.remove('hidden');
}

function cerrarModal() {
    document.getElementById('modalActualizar').classList.add('hidden');
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalActualizar').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});
</script>
@endsection