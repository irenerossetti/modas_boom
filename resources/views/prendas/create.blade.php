<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-boom-text-dark">Nueva Prenda</h2>
                            <p class="text-sm text-boom-text-medium mt-1">Añade una nueva prenda al catálogo</p>
                        </div>
                        <a href="{{ route('prendas.index') }}" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('prendas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información Básica -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-boom-text-dark border-b border-boom-cream-300 pb-2">
                                    Información Básica
                                </h3>

                                <div>
                                    <label for="nombre" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        Nombre de la Prenda *
                                    </label>
                                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                                           class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50"
                                           placeholder="Ej: Traje Ejecutivo Clásico">
                                </div>

                                <div>
                                    <label for="categoria" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        Categoría *
                                    </label>
                                    <select name="categoria" id="categoria" required
                                            class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                        <option value="">Seleccionar categoría</option>
                                        <option value="Formal" {{ old('categoria') == 'Formal' ? 'selected' : '' }}>Formal</option>
                                        <option value="Informal" {{ old('categoria') == 'Informal' ? 'selected' : '' }}>Informal</option>
                                        <option value="Deportiva" {{ old('categoria') == 'Deportiva' ? 'selected' : '' }}>Deportiva</option>
                                        <option value="Accesorios" {{ old('categoria') == 'Accesorios' ? 'selected' : '' }}>Accesorios</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="precio" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        Precio (Bs.) *
                                    </label>
                                    <input type="number" name="precio" id="precio" value="{{ old('precio') }}" 
                                           step="0.01" min="0" required
                                           class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50"
                                           placeholder="0.00">
                                </div>

                                <div>
                                    <label for="stock" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        Stock Inicial *
                                    </label>
                                    <input type="number" name="stock" id="stock" value="{{ old('stock') }}" 
                                           min="0" required
                                           class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50"
                                           placeholder="0">
                                    <p class="text-xs text-boom-text-medium mt-1">Cantidad de unidades disponibles</p>
                                </div>

                                <div>
                                    <label for="descripcion" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        Descripción
                                    </label>
                                    <textarea name="descripcion" id="descripcion" rows="4"
                                              class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50"
                                              placeholder="Descripción detallada de la prenda...">{{ old('descripcion') }}</textarea>
                                </div>
                            </div>

                            <!-- Detalles y Configuración -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-boom-text-dark border-b border-boom-cream-300 pb-2">
                                    Detalles y Configuración
                                </h3>

                                <div>
                                    <label for="imagen" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        Imagen de la Prenda
                                    </label>
                                    <input type="file" name="imagen" id="imagen" accept="image/*"
                                           class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                    <p class="text-xs text-boom-text-medium mt-1">Formatos: JPG, PNG, GIF. Máximo 2MB</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-boom-text-dark mb-2">
                                        Colores Disponibles
                                    </label>
                                    <div id="colores-container" class="space-y-2">
                                        <div class="flex gap-2">
                                            <input type="text" name="colores[]" placeholder="Ej: Negro, Azul Marino..."
                                                   class="flex-1 rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                            <button type="button" onclick="agregarColor()" class="bg-boom-rose-dark text-white px-3 py-2 rounded-md hover:bg-boom-rose-light transition-colors">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-boom-text-dark mb-2">
                                        Tallas Disponibles
                                    </label>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Única'] as $talla)
                                            <label class="flex items-center">
                                                <input type="checkbox" name="tallas[]" value="{{ $talla }}" 
                                                       {{ in_array($talla, old('tallas', [])) ? 'checked' : '' }}
                                                       class="rounded border-boom-cream-300 text-boom-rose-dark shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-boom-text-dark">{{ $talla }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}
                                               class="rounded border-boom-cream-300 text-boom-rose-dark shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                        <span class="ml-2 text-sm font-medium text-boom-text-dark">Prenda activa</span>
                                    </label>
                                    <p class="text-xs text-boom-text-medium mt-1">Las prendas activas aparecen en el catálogo</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('prendas.index') }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                <i class="fas fa-save mr-2"></i>Guardar Prenda
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function agregarColor() {
            const container = document.getElementById('colores-container');
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <input type="text" name="colores[]" placeholder="Ej: Rojo, Verde..."
                       class="flex-1 rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                <button type="button" onclick="removerColor(this)" class="bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600 transition-colors">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }

        function removerColor(button) {
            button.parentElement.remove();
        }
    </script>
</x-app-layout>