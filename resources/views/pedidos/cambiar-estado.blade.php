@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-exchange-alt text-blue-600 mr-2"></i>
                        Cambiar Estado del Pedido
                    </h1>
                    <p class="text-gray-600 mt-1">Pedido #{{ $pedido->id_pedido }} - {{ $pedido->cliente->nombre }}</p>
                </div>
                <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>

        <!-- Información Actual -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Estado Actual
            </h2>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <span class="text-gray-600">Estado actual:</span>
                    <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium
                        @if($pedido->estado == 'Pendiente') bg-yellow-100 text-yellow-800
                        @elseif($pedido->estado == 'En proceso') bg-blue-100 text-blue-800
                        @elseif($pedido->estado == 'Asignado') bg-purple-100 text-purple-800
                        @elseif($pedido->estado == 'En producción') bg-orange-100 text-orange-800
                        @elseif($pedido->estado == 'Terminado') bg-green-100 text-green-800
                        @elseif($pedido->estado == 'Entregado') bg-gray-100 text-gray-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ $pedido->estado }}
                    </span>
                </div>
                <div class="text-sm text-gray-500">
                    Cliente: {{ $pedido->cliente->email ?? 'Sin email' }}
                </div>
            </div>
        </div>

        <!-- Formulario de Cambio -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-edit text-green-500 mr-2"></i>Nuevo Estado
            </h2>

            <form action="{{ route('pedidos.cambiar-estado-con-notificacion', $pedido->id_pedido) }}" method="POST">
                @csrf
                
                <!-- Nuevo Estado -->
                <div class="mb-6">
                    <label for="nuevo_estado" class="block text-sm font-medium text-gray-700 mb-2">
                        Nuevo Estado *
                    </label>
                    <select id="nuevo_estado" name="nuevo_estado" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nuevo_estado') border-red-500 @enderror">
                        <option value="">Seleccionar nuevo estado...</option>
                        @foreach($estadosDisponibles as $key => $estado)
                            @if($key !== $pedido->estado)
                                <option value="{{ $key }}" {{ old('nuevo_estado') == $key ? 'selected' : '' }}>
                                    {{ $estado }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('nuevo_estado')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones -->
                <div class="mb-6">
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                        Observaciones (Opcional)
                    </label>
                    <textarea id="observaciones" name="observaciones" rows="4"
                              placeholder="Agregue observaciones sobre el cambio de estado..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('observaciones') border-red-500 @enderror">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Información de Notificación -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-envelope text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Notificación Automática:</strong> 
                                @if($pedido->cliente && $pedido->cliente->email)
                                    Se enviará una notificación por email a <strong>{{ $pedido->cliente->email }}</strong> informando sobre el cambio de estado.
                                @else
                                    <span class="text-red-600">El cliente no tiene email registrado. No se enviará notificación.</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-paper-plane mr-2"></i>Cambiar Estado y Notificar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.getElementById('nuevo_estado');
    
    estadoSelect.addEventListener('change', function() {
        const estadoSeleccionado = this.value;
        const observacionesField = document.getElementById('observaciones');
        
        // Sugerir observaciones según el estado
        switch(estadoSeleccionado) {
            case 'En proceso':
                observacionesField.placeholder = 'Ej: Pedido recibido y en proceso de revisión...';
                break;
            case 'Asignado':
                observacionesField.placeholder = 'Ej: Pedido asignado al operario Juan Pérez...';
                break;
            case 'En producción':
                observacionesField.placeholder = 'Ej: Iniciada la producción, tiempo estimado 3 días...';
                break;
            case 'Terminado':
                observacionesField.placeholder = 'Ej: Pedido terminado y listo para entrega...';
                break;
            case 'Entregado':
                observacionesField.placeholder = 'Ej: Pedido entregado exitosamente al cliente...';
                break;
            case 'Cancelado':
                observacionesField.placeholder = 'Ej: Pedido cancelado por solicitud del cliente...';
                break;
            default:
                observacionesField.placeholder = 'Agregue observaciones sobre el cambio de estado...';
        }
    });
});
</script>
@endsection