<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-tasks text-orange-600 mr-2"></i>
                                Registrar Avance de Producción
                            </h1>
                            <p class="text-gray-600 mt-1">Pedido #{{ $pedido->id_pedido }} - {{ $pedido->cliente->nombre }}</p>
                        </div>
                        <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Nuevo Avance</h2>
                </div>
                <div class="px-6 py-4">
                    <form action="{{ route('pedidos.procesar-avance', $pedido->id_pedido) }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Etapa -->
                            <div>
                                <label for="etapa" class="block text-sm font-medium text-gray-700 mb-2">
                                    Etapa de Producción *
                                </label>
                                <select id="etapa" name="etapa" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Seleccionar etapa...</option>
                                    @foreach($etapas as $key => $etapa)
                                        <option value="{{ $key }}" {{ old('etapa') == $key ? 'selected' : '' }}>
                                            {{ $etapa }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('etapa')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Porcentaje -->
                            <div>
                                <label for="porcentaje_avance" class="block text-sm font-medium text-gray-700 mb-2">
                                    Porcentaje de Avance *
                                </label>
                                <div class="relative">
                                    <input type="number" id="porcentaje_avance" name="porcentaje_avance" 
                                           min="0" max="100" value="{{ old('porcentaje_avance') }}" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <span class="absolute right-3 top-2 text-gray-500">%</span>
                                </div>
                                @error('porcentaje_avance')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mt-6">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción del Avance *
                            </label>
                            <textarea id="descripcion" name="descripcion" rows="3" required
                                      placeholder="Describe el trabajo realizado en esta etapa..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observaciones -->
                        <div class="mt-6">
                            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                                Observaciones (Opcional)
                            </label>
                            <textarea id="observaciones" name="observaciones" rows="3"
                                      placeholder="Observaciones adicionales, problemas encontrados, etc..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg transition-colors">
                                <i class="fas fa-save mr-2"></i>Registrar Avance
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Avances Anteriores -->
            @if($avancesAnteriores->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Avances Anteriores</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        @foreach($avancesAnteriores as $avance)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-sm font-medium">
                                        {{ $avance->etapa }}
                                    </span>
                                    <span class="ml-3 text-lg font-bold text-orange-600">
                                        {{ $avance->porcentaje_avance }}%
                                    </span>
                                </div>
                                <span class="text-sm text-gray-500">
                                    {{ $avance->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <p class="text-gray-700 mb-2">{{ $avance->descripcion }}</p>
                            @if($avance->observaciones)
                            <p class="text-sm text-gray-600 italic">{{ $avance->observaciones }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-2">
                                Registrado por: {{ $avance->registradoPor->nombre ?? 'Usuario no encontrado' }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>