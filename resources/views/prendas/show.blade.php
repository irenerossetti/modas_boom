<x-app-layout>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-boom-text-dark">{{ $prenda->nombre }}</h2>
                            <p class="text-sm text-boom-text-medium mt-1">{{ $prenda->categoria }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('prendas.edit', $prenda) }}" 
                               class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                <i class="fas fa-edit mr-2"></i>Editar
                            </a>
                            <a href="{{ route('prendas.index') }}" 
                               class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                <i class="fas fa-arrow-left mr-2"></i>Volver
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Imagen y Estado -->
                        <div>
                            <div class="relative bg-boom-cream-100 rounded-xl overflow-hidden mb-4">
                                @if($prenda->imagen)
                                    <img src="{{ asset($prenda->imagen) }}" 
                                         alt="{{ $prenda->nombre }}" 
                                         class="w-full h-96 object-cover">
                                @else
                                    <div class="w-full h-96 flex items-center justify-center text-boom-text-medium">
                                        <div class="text-center">
                                            <i class="fas fa-image text-6xl mb-4"></i>
                                            <p>Sin imagen</p>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Badge de estado -->
                                <div class="absolute top-4 right-4">
                                    @if($prenda->activo)
                                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                            <i class="fas fa-check mr-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                            <i class="fas fa-times mr-1"></i>Inactivo
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Estadísticas rápidas -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ $prenda->stock }}</div>
                                    <div class="text-sm text-blue-800">Stock Actual</div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $prenda->precio_formateado }}</div>
                                    <div class="text-sm text-green-800">Precio</div>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ $prenda->pedidos->count() ?? 0 }}</div>
                                    <div class="text-sm text-purple-800">Pedidos</div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Detallada -->
                        <div class="space-y-6">
                            <!-- Información Básica -->
                            <div class="bg-boom-cream-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-boom-text-dark mb-3">Información Básica</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-boom-text-dark">Nombre:</span>
                                        <span class="text-boom-text-medium">{{ $prenda->nombre }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-boom-text-dark">Categoría:</span>
                                        <span class="text-boom-text-medium">{{ $prenda->categoria }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-boom-text-dark">Precio:</span>
                                        <span class="text-boom-text-medium font-bold text-boom-rose-dark">{{ $prenda->precio_formateado }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-boom-text-dark">Stock:</span>
                                        <span class="text-boom-text-medium">
                                            <span class="font-bold {{ $prenda->stock > 10 ? 'text-green-600' : ($prenda->stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $prenda->stock }} unidades
                                            </span>
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-boom-text-dark">Estado:</span>
                                        <span class="text-boom-text-medium">
                                            @if($prenda->activo)
                                                <span class="text-green-600 font-semibold">Activo</span>
                                            @else
                                                <span class="text-red-600 font-semibold">Inactivo</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción -->
                            @if($prenda->descripcion)
                            <div class="bg-boom-cream-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-boom-text-dark mb-3">Descripción</h3>
                                <p class="text-boom-text-medium leading-relaxed">{{ $prenda->descripcion }}</p>
                            </div>
                            @endif

                            <!-- Colores Disponibles -->
                            @if($prenda->colores && count($prenda->colores) > 0)
                            <div class="bg-boom-cream-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-boom-text-dark mb-3">Colores Disponibles</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($prenda->colores as $color)
                                        <span class="bg-boom-rose-dark text-white px-3 py-1 rounded-full text-sm">{{ $color }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Tallas Disponibles -->
                            @if($prenda->tallas && count($prenda->tallas) > 0)
                            <div class="bg-boom-cream-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-boom-text-dark mb-3">Tallas Disponibles</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($prenda->tallas as $talla)
                                        <span class="bg-boom-cream-300 text-boom-text-dark px-3 py-1 rounded-full text-sm font-semibold">{{ $talla }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Fechas -->
                            <div class="bg-boom-cream-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-boom-text-dark mb-3">Información de Registro</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-boom-text-dark">Creado:</span>
                                        <span class="text-boom-text-medium">{{ $prenda->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if($prenda->updated_at != $prenda->created_at)
                                    <div class="flex justify-between">
                                        <span class="font-medium text-boom-text-dark">Última actualización:</span>
                                        <span class="text-boom-text-medium">{{ $prenda->updated_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="mt-8 flex justify-between items-center pt-6 border-t border-boom-cream-300">
                        <div class="flex space-x-4">
                            <a href="{{ route('prendas.edit', $prenda) }}" 
                               class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                <i class="fas fa-edit mr-2"></i>Editar Prenda
                            </a>
                            
                            <form action="{{ route('prendas.destroy', $prenda) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('¿Estás seguro de eliminar esta prenda?\n\nEsta acción no se puede deshacer.')"
                                        class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                    <i class="fas fa-trash mr-2"></i>Eliminar Prenda
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('prendas.index') }}" 
                           class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>