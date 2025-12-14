@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-credit-card mr-3 text-blue-600"></i>
            Detalles del Método de Pago
        </h1>
        <div class="flex space-x-2">
            <a href="{{ route('metodos-pago.edit', $metodoPago) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            <a href="{{ route('metodos-pago.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información básica -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 mr-3">
                                <div class="h-10 w-10 rounded-lg flex items-center justify-center" 
                                     style="background-color: {{ $metodoPago->color }}20; color: {{ $metodoPago->color }}">
                                    <i class="{{ $metodoPago->icono }} text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-900">{{ $metodoPago->nombre }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                            @if($metodoPago->tipo === 'automatico') bg-green-100 text-green-800
                            @elseif($metodoPago->tipo === 'qr') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($metodoPago->tipo) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                            {{ $metodoPago->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $metodoPago->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                        <p class="text-gray-900">{{ $metodoPago->orden }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <div class="flex items-center">
                            <div class="w-6 h-6 rounded border border-gray-300 mr-2" 
                                 style="background-color: {{ $metodoPago->color }}"></div>
                            <span class="text-gray-900">{{ $metodoPago->color }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icono</label>
                        <div class="flex items-center">
                            <i class="{{ $metodoPago->icono }} text-xl mr-2" style="color: {{ $metodoPago->color }}"></i>
                            <span class="text-gray-900">{{ $metodoPago->icono }}</span>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">
                            {{ $metodoPago->descripcion ?: 'Sin descripción' }}
                        </p>
                    </div>

                    @if($metodoPago->qr_image)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Imagen QR</label>
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <img src="{{ $metodoPago->qr_image_url }}" 
                                 alt="QR {{ $metodoPago->nombre }}" 
                                 class="w-32 h-32 mx-auto rounded border">
                            <p class="text-sm text-gray-600 mt-2">{{ basename($metodoPago->qr_image) }}</p>
                        </div>
                    </div>
                    @endif

                    @if($metodoPago->configuracion)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Configuración</label>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <pre class="text-sm text-gray-900">{{ json_encode($metodoPago->configuracion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fechas</label>
                        <div class="bg-gray-50 p-3 rounded-lg space-y-1">
                            <p class="text-sm"><strong>Creado:</strong> {{ $metodoPago->created_at->format('d/m/Y H:i:s') }}</p>
                            <p class="text-sm"><strong>Actualizado:</strong> {{ $metodoPago->updated_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div class="flex space-x-2">
                    <form action="{{ route('metodos-pago.toggle-active', $metodoPago) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200
                                {{ $metodoPago->activo ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                            <i class="fas fa-{{ $metodoPago->activo ? 'pause' : 'play' }} mr-1"></i>
                            {{ $metodoPago->activo ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('metodos-pago.edit', $metodoPago) }}" 
                       class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Editar
                    </a>
                    <form action="{{ route('metodos-pago.destroy', $metodoPago) }}" method="POST" class="inline"
                          onsubmit="return confirm('¿Estás seguro de eliminar este método de pago?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            <i class="fas fa-trash mr-2"></i>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection