@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="{{ route('metodos-pago.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-edit mr-3 text-yellow-600"></i>
                Editar Método de Pago
            </h1>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('metodos-pago.update', $metodoPago) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Método</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $metodoPago->nombre) }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nombre') border-red-500 @enderror"
                               placeholder="Ej: Efectivo, Tarjeta de Crédito">
                        @error('nombre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Método</label>
                        <select name="tipo" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tipo') border-red-500 @enderror">
                            <option value="">Seleccionar tipo</option>
                            <option value="manual" {{ old('tipo', $metodoPago->tipo) === 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="automatico" {{ old('tipo', $metodoPago->tipo) === 'automatico' ? 'selected' : '' }}>Automático</option>
                            <option value="qr" {{ old('tipo', $metodoPago->tipo) === 'qr' ? 'selected' : '' }}>QR Code</option>
                        </select>
                        @error('tipo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Icono (Solo lectura) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Icono</label>
                        <div class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                            <i class="{{ $metodoPago->icono }} mr-2" style="color: {{ $metodoPago->color }}"></i>
                            {{ $metodoPago->icono }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">El icono no se puede modificar después de la creación.</p>
                    </div>

                    <!-- Color (Solo lectura) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                        <div class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                            <div class="flex items-center">
                                <div class="w-6 h-6 rounded border border-gray-300 mr-2" style="background-color: {{ $metodoPago->color }}"></div>
                                {{ $metodoPago->color }}
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">El color no se puede modificar después de la creación.</p>
                    </div>

                    <!-- Orden -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Orden de Visualización</label>
                        <input type="number" name="orden" value="{{ old('orden', $metodoPago->orden) }}" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('orden') border-red-500 @enderror">
                        @error('orden')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="activo" value="1" {{ old('activo', $metodoPago->activo) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Método activo</span>
                        </label>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea name="descripcion" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descripcion') border-red-500 @enderror"
                              placeholder="Descripción opcional del método de pago">{{ old('descripcion', $metodoPago->descripcion) }}</textarea>
                    @error('descripcion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Imagen QR -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Imagen QR</label>
                    
                    @if($metodoPago->qr_image)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">Imagen actual:</p>
                            <img src="{{ $metodoPago->qr_image_url }}" alt="QR actual" class="w-32 h-32 object-cover rounded border">
                        </div>
                    @endif
                    
                    <input type="file" name="qr_image" accept="image/*"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qr_image') border-red-500 @enderror">
                    @error('qr_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $metodoPago->qr_image ? 'Selecciona una nueva imagen para reemplazar la actual.' : 'Solo para métodos tipo QR.' }} 
                        Formatos: JPG, PNG, GIF. Máximo 2MB.
                    </p>
                </div>

                <!-- Botones -->
                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ route('metodos-pago.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Actualizar Método
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection