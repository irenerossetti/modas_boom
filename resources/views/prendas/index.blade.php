<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-boom-text-dark">Gestión de Prendas</h2>
                            <p class="text-sm text-boom-text-medium mt-1">Administra el catálogo de productos</p>
                        </div>
                        <a href="{{ route('prendas.create') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                            <i class="fas fa-plus mr-2"></i>Nueva Prenda
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filtros -->
                    <div class="mb-6 bg-boom-cream-100 p-4 rounded-lg">
                        <form method="GET" action="{{ route('prendas.index') }}" class="flex flex-wrap gap-4 items-end">
                            <div class="flex-1 min-w-64">
                                <label class="block text-sm font-medium text-boom-text-dark mb-1">Buscar</label>
                                <input type="text" name="busqueda" value="{{ request('busqueda') }}" 
                                       placeholder="Nombre o descripción..." 
                                       class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                            </div>
                            <div class="min-w-48">
                                <label class="block text-sm font-medium text-boom-text-dark mb-1">Categoría</label>
                                <select name="categoria" class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                    <option value="">Todas las categorías</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria }}" {{ request('categoria') == $categoria ? 'selected' : '' }}>
                                            {{ $categoria }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="min-w-32">
                                <label class="block text-sm font-medium text-boom-text-dark mb-1">Estado</label>
                                <select name="activo" class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                    <option value="">Todos</option>
                                    <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activos</option>
                                    <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white px-4 py-2 rounded-md transition-colors">
                                    <i class="fas fa-search mr-1"></i>Filtrar
                                </button>
                                <a href="{{ route('prendas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors">
                                    <i class="fas fa-times mr-1"></i>Limpiar
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de Prendas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @forelse($prendas as $prenda)
                            <div class="bg-white border border-boom-cream-300 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300">
                                <!-- Imagen -->
                                <div class="relative h-48 bg-boom-cream-100">
                                    @if($prenda->imagen)
                                        <img src="{{ asset($prenda->imagen) }}" 
                                             alt="{{ $prenda->nombre }}" 
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-boom-text-medium">
                                            <i class="fas fa-image text-4xl"></i>
                                        </div>
                                    @endif
                                    
                                    <!-- Badge de estado -->
                                    <div class="absolute top-2 right-2">
                                        @if($prenda->activo)
                                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Activo</span>
                                        @else
                                            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">Inactivo</span>
                                        @endif
                                    </div>

                                    <!-- Badge de stock -->
                                    <div class="absolute top-2 left-2">
                                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                            <i class="fas fa-box mr-1"></i>{{ $prenda->stock }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Contenido -->
                                <div class="p-4">
                                    <div class="mb-2">
                                        <h3 class="font-semibold text-boom-text-dark text-lg">{{ $prenda->nombre }}</h3>
                                        <p class="text-sm text-boom-text-medium">{{ $prenda->categoria }}</p>
                                    </div>

                                    <p class="text-sm text-boom-text-medium mb-3 line-clamp-2">
                                        {{ Str::limit($prenda->descripcion, 80) }}
                                    </p>

                                    <div class="mb-3">
                                        <p class="text-xl font-bold text-boom-rose-dark">{{ $prenda->precio_formateado }}</p>
                                    </div>

                                    <!-- Colores y Tallas -->
                                    @if($prenda->colores && count($prenda->colores) > 0)
                                        <div class="mb-2">
                                            <p class="text-xs text-boom-text-medium mb-1">Colores:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach(array_slice($prenda->colores, 0, 3) as $color)
                                                    <span class="text-xs bg-boom-cream-400 text-boom-text-dark border border-boom-cream-500 px-2 py-1 rounded font-medium">{{ $color }}</span>
                                                @endforeach
                                                @if(count($prenda->colores) > 3)
                                                    <span class="text-xs text-boom-text-medium">+{{ count($prenda->colores) - 3 }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if($prenda->tallas && count($prenda->tallas) > 0)
                                        <div class="mb-3">
                                            <p class="text-xs text-boom-text-medium mb-1">Tallas:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($prenda->tallas as $talla)
                                                    <span class="text-xs bg-boom-cream-400 text-boom-text-dark border border-boom-cream-500 px-2 py-1 rounded font-medium">{{ $talla }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Acciones -->
                                    <div class="flex gap-2">
                                        <a href="{{ route('prendas.show', $prenda) }}" 
                                           class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-3 rounded text-sm transition-colors">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                        <a href="{{ route('prendas.edit', $prenda) }}" 
                                           class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-center py-2 px-3 rounded text-sm transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </a>
                                        <form action="{{ route('prendas.destroy', $prenda) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirm('¿Estás seguro de eliminar esta prenda?')"
                                                    class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded text-sm transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <i class="fas fa-tshirt text-6xl text-boom-text-light mb-4"></i>
                                <h3 class="text-xl font-semibold text-boom-text-dark mb-2">No hay prendas</h3>
                                <p class="text-boom-text-medium mb-4">No se encontraron prendas con los filtros aplicados.</p>
                                <a href="{{ route('prendas.create') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                    <i class="fas fa-plus mr-2"></i>Crear Primera Prenda
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Paginación -->
                    @if($prendas->hasPages())
                        <div class="mt-6">
                            {{ $prendas->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>