@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Reprogramar Entrega</h1>
                    <p class="text-gray-600 mt-1">Pedido #{{ $pedido->id_pedido }}</p>
                </div>
                <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Información del Pedido -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>Información del Pedido
                </h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cliente:</span>
                        <span class="font-medium">{{ $pedido->cliente->nombre }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Estado:</span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($pedido->estado == 'Pendiente') bg-yellow-100 text-yellow-800
                            @elseif($pedido->estado == 'En proceso') bg-blue-100 text-blue-800
                            @elseif($pedido->estado == 'Terminado') bg-green-100 text-green-800
                            @elseif($pedido->estado == 'Entregado') bg-gray-100 text-gray-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $pedido->estado }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fecha Actual de Entrega:</span>
                        <span class="font-medium">
                            {{ $pedido->fecha_entrega_programada ? \Carbon\Carbon::parse($pedido->fecha_entrega_programada)->format('d/m/Y') : 'Sin fecha programada' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total:</span>
                        <span class="font-medium">${{ number_format($pedido->total, 2) }}</span>
                    </div>
                </div>

                <!-- Productos del Pedido -->
                <div class="mt-6">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Productos</h3>
                    <div class="space-y-2">
                        @foreach($pedido->prendas as $prenda)
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span class="text-sm">{{ $prenda->nombre }}</span>
                            <span class="text-sm font-medium">{{ $prenda->pivot->cantidad }} x ${{ number_format($prenda->pivot->precio_unitario, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Formulario de Reprogramación -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-calendar-alt text-green-500 mr-2"></i>Nueva Fecha de Entrega
                </h2>

                <form action="{{ route('pedidos.procesar-reprogramacion', $pedido->id_pedido) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="nueva_fecha_entrega" class="block text-sm font-medium text-gray-700 mb-2">
                            Nueva Fecha de Entrega *
                        </label>
                        <input type="date" 
                               id="nueva_fecha_entrega" 
                               name="nueva_fecha_entrega" 
                               value="{{ old('nueva_fecha_entrega') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nueva_fecha_entrega') border-red-500 @enderror"
                               required>
                        @error('nueva_fecha_entrega')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="motivo_reprogramacion" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo de la Reprogramación *
                        </label>
                        <textarea id="motivo_reprogramacion" 
                                  name="motivo_reprogramacion" 
                                  rows="4" 
                                  placeholder="Explique el motivo de la reprogramación..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('motivo_reprogramacion') border-red-500 @enderror"
                                  required>{{ old('motivo_reprogramacion') }}</textarea>
                        @error('motivo_reprogramacion')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="submit" 
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-save mr-2"></i>Reprogramar Entrega
                        </button>
                        <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                           class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-center transition duration-200">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Historial de Reprogramaciones -->
        @if($historialReprogramaciones->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-history text-purple-500 mr-2"></i>Historial de Reprogramaciones
            </h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($historialReprogramaciones as $registro)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->created_at ? $registro->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->nombre_usuario }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $registro->descripcion }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Establecer fecha mínima como mañana
    const fechaInput = document.getElementById('nueva_fecha_entrega');
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    fechaInput.min = tomorrow.toISOString().split('T')[0];
});
</script>
@endsection