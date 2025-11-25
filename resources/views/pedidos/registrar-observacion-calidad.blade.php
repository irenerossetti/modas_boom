@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-clipboard-check text-green-600 mr-2"></i>
                        Registrar Observación de Calidad
                    </h1>
                    <p class="text-gray-600 mt-1">Pedido #{{ $pedido->id_pedido }} - {{ $pedido->cliente->nombre }}</p>
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
                            @elseif($pedido->estado == 'En producción') bg-orange-100 text-orange-800
                            @elseif($pedido->estado == 'Terminado') bg-green-100 text-green-800
                            @elseif($pedido->estado == 'Entregado') bg-gray-100 text-gray-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $pedido->estado }}
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

            <!-- Formulario de Observación -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-clipboard-check text-green-500 mr-2"></i>Nueva Observación de Calidad
                </h2>

                <form action="{{ route('pedidos.procesar-observacion-calidad', $pedido->id_pedido) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Tipo de Observación -->
                        <div>
                            <label for="tipo_observacion" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Observación *
                            </label>
                            <select id="tipo_observacion" name="tipo_observacion" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('tipo_observacion') border-red-500 @enderror">
                                <option value="">Seleccionar tipo...</option>
                                @foreach($tiposObservacion as $key => $tipo)
                                    <option value="{{ $key }}" {{ old('tipo_observacion') == $key ? 'selected' : '' }}>
                                        {{ $tipo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_observacion')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prioridad -->
                        <div>
                            <label for="prioridad" class="block text-sm font-medium text-gray-700 mb-2">
                                Prioridad *
                            </label>
                            <select id="prioridad" name="prioridad" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('prioridad') border-red-500 @enderror">
                                <option value="">Seleccionar prioridad...</option>
                                @foreach($prioridades as $key => $prioridad)
                                    <option value="{{ $key }}" {{ old('prioridad') == $key ? 'selected' : '' }}>
                                        {{ $prioridad }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prioridad')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Área Afectada -->
                    <div>
                        <label for="area_afectada" class="block text-sm font-medium text-gray-700 mb-2">
                            Área Afectada *
                        </label>
                        <input type="text" 
                               id="area_afectada" 
                               name="area_afectada" 
                               value="{{ old('area_afectada') }}"
                               placeholder="Ej: Costura, Tela, Acabado, Botones, etc."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('area_afectada') border-red-500 @enderror"
                               required>
                        @error('area_afectada')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción de la Observación *
                        </label>
                        <textarea id="descripcion" 
                                  name="descripcion" 
                                  rows="4" 
                                  placeholder="Describe detalladamente la observación de calidad..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('descripcion') border-red-500 @enderror"
                                  required>{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acción Correctiva -->
                    <div>
                        <label for="accion_correctiva" class="block text-sm font-medium text-gray-700 mb-2">
                            Acción Correctiva (Opcional)
                        </label>
                        <textarea id="accion_correctiva" 
                                  name="accion_correctiva" 
                                  rows="3" 
                                  placeholder="Describe la acción correctiva a tomar..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('accion_correctiva') border-red-500 @enderror">{{ old('accion_correctiva') }}</textarea>
                        @error('accion_correctiva')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="submit" 
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-save mr-2"></i>Registrar Observación
                        </button>
                        <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                           class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-center transition duration-200">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Observaciones Anteriores -->
        @if($observacionesAnteriores->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-history text-purple-500 mr-2"></i>Observaciones Anteriores
            </h2>
            
            <div class="space-y-4">
                @foreach($observacionesAnteriores as $observacion)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-3">
                            <span class="px-2 py-1 rounded text-sm font-medium
                                @if($observacion->tipo_observacion == 'Defecto') bg-red-100 text-red-800
                                @elseif($observacion->tipo_observacion == 'Mejora') bg-blue-100 text-blue-800
                                @elseif($observacion->tipo_observacion == 'Aprobado') bg-green-100 text-green-800
                                @elseif($observacion->tipo_observacion == 'Rechazado') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $observacion->tipo_observacion }}
                            </span>
                            <span class="px-2 py-1 rounded text-sm font-medium
                                @if($observacion->prioridad == 'Crítica') bg-red-100 text-red-800
                                @elseif($observacion->prioridad == 'Alta') bg-orange-100 text-orange-800
                                @elseif($observacion->prioridad == 'Media') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $observacion->prioridad }}
                            </span>
                            <span class="px-2 py-1 rounded text-sm font-medium
                                @if($observacion->estado == 'Pendiente') bg-yellow-100 text-yellow-800
                                @elseif($observacion->estado == 'En corrección') bg-blue-100 text-blue-800
                                @elseif($observacion->estado == 'Corregido') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $observacion->estado }}
                            </span>
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ $observacion->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <p class="text-gray-700 mb-2"><strong>Área:</strong> {{ $observacion->area_afectada }}</p>
                    <p class="text-gray-700 mb-2">{{ $observacion->descripcion }}</p>
                    @if($observacion->accion_correctiva)
                    <p class="text-sm text-gray-600 italic"><strong>Acción correctiva:</strong> {{ $observacion->accion_correctiva }}</p>
                    @endif
                    <p class="text-xs text-gray-500 mt-2">
                        Registrado por: {{ $observacion->registradoPor->nombre ?? 'Usuario no encontrado' }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection