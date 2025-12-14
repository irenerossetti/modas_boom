@extends('layouts.app')

@section('content')
    <div class="p-2 sm:p-4 lg:p-6">
        <div class="flex justify-between items-center mb-4 lg:mb-6">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-tshirt mr-2"></i>
                <span class="hidden sm:inline">Catálogo de Productos</span>
                <span class="sm:hidden">Catálogo</span>
            </h1>
        </div>

        <!-- Buscador y Filtros -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form method="GET" action="{{ route('catalogo.index') }}" class="space-y-4">
                <!-- Barra de búsqueda principal -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="busqueda" value="{{ $filtros['busqueda'] ?? '' }}" 
                               placeholder="Buscar productos por nombre, descripción..." 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-boom-primary focus:border-transparent">
                    </div>
                    <button type="submit" class="bg-boom-primary hover:bg-boom-primary-dark text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                </div>

                <!-- Filtros avanzados -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select name="categoria" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-boom-primary focus:border-transparent">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria }}" {{ ($filtros['categoria'] ?? '') == $categoria ? 'selected' : '' }}>
                                    {{ $categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio mínimo</label>
                        <input type="number" name="precio_min" value="{{ $filtros['precio_min'] ?? '' }}" 
                               placeholder="Bs. 0" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-boom-primary focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio máximo</label>
                        <input type="number" name="precio_max" value="{{ $filtros['precio_max'] ?? '' }}" 
                               placeholder="Bs. 1000" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-boom-primary focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordenar por</label>
                        <select name="ordenar" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-boom-primary focus:border-transparent">
                            <option value="categoria" {{ ($filtros['ordenar'] ?? '') == 'categoria' ? 'selected' : '' }}>Categoría</option>
                            <option value="nombre_asc" {{ ($filtros['ordenar'] ?? '') == 'nombre_asc' ? 'selected' : '' }}>Nombre A-Z</option>
                            <option value="nombre_desc" {{ ($filtros['ordenar'] ?? '') == 'nombre_desc' ? 'selected' : '' }}>Nombre Z-A</option>
                            <option value="precio_asc" {{ ($filtros['ordenar'] ?? '') == 'precio_asc' ? 'selected' : '' }}>Precio menor a mayor</option>
                            <option value="precio_desc" {{ ($filtros['ordenar'] ?? '') == 'precio_desc' ? 'selected' : '' }}>Precio mayor a menor</option>
                        </select>
                    </div>
                </div>

                <!-- Botones de acción y estadísticas -->
                <div class="flex flex-wrap gap-2 justify-between items-center">
                    <div class="flex gap-2">
                        <a href="{{ route('catalogo.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-times mr-1"></i>Limpiar filtros
                        </a>
                    </div>
                    
                    <!-- Estadísticas de búsqueda -->
                    @if(isset($estadisticas))
                        <div class="text-sm text-gray-600">
                            @if(($filtros['busqueda'] ?? '') || ($filtros['categoria'] ?? '') || ($filtros['precio_min'] ?? '') || ($filtros['precio_max'] ?? ''))
                                Mostrando {{ $productos->count() }} de {{ $estadisticas['productos_filtrados'] }} productos
                            @else
                                {{ $estadisticas['total_productos'] }} productos disponibles
                            @endif
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <!-- Filtros rápidos por categoría -->
        @if($categorias->count() > 0)
            <div class="bg-white p-3 rounded-lg shadow mb-4">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('catalogo.index') }}" 
                       class="px-3 py-1 rounded-full text-sm font-medium transition-colors {{ !($filtros['categoria'] ?? '') ? 'bg-boom-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-boom-primary hover:text-white' }}">
                        <i class="fas fa-th-large mr-1"></i>Todos
                    </a>
                    @foreach($categorias as $categoria)
                        <a href="{{ route('catalogo.index', ['categoria' => $categoria]) }}" 
                           class="px-3 py-1 rounded-full text-sm font-medium transition-colors {{ ($filtros['categoria'] ?? '') == $categoria ? 'bg-boom-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-boom-primary hover:text-white' }}">
                            @if(strtolower($categoria) == 'formal')
                                <i class="fas fa-user-tie mr-1"></i>
                            @elseif(strtolower($categoria) == 'informal')
                                <i class="fas fa-tshirt mr-1"></i>
                            @else
                                <i class="fas fa-tag mr-1"></i>
                            @endif
                            {{ $categoria }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Catálogo de productos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
            @forelse($productos as $producto)
                <div class="producto-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="relative">
                        @if($producto->imagen && file_exists(public_path($producto->imagen)))
                            <img src="{{ asset($producto->imagen) }}" 
                                 alt="{{ $producto->nombre }}" 
                                 class="w-full h-64 object-cover">
                        @else
                            <div class="w-full h-64 bg-boom-cream-200 flex items-center justify-center">
                                <i class="fas fa-tshirt text-4xl text-boom-text-medium"></i>
                            </div>
                        @endif
                        <div class="absolute top-2 right-2">
                            <span class="bg-boom-primary text-white px-2 py-1 rounded-full text-xs font-medium">
                                {{ $producto->categoria }}
                            </span>
                        </div>
                        @if($producto->stock <= 5)
                            <div class="absolute top-2 left-2">
                                <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                    Stock Bajo
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-boom-text-dark mb-2">{{ $producto->nombre }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($producto->descripcion, 80) }}</p>
                        
                        <!-- Colores disponibles -->
                        @if($producto->colores && count($producto->colores) > 0)
                            <div class="mb-3">
                                <p class="text-xs text-gray-500 mb-1">Colores:</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($producto->colores, 0, 3) as $color)
                                        <span class="text-xs bg-boom-cream-300 text-boom-text-dark px-2 py-1 rounded">{{ $color }}</span>
                                    @endforeach
                                    @if(count($producto->colores) > 3)
                                        <span class="text-xs bg-boom-cream-400 text-boom-text-dark px-2 py-1 rounded">+{{ count($producto->colores) - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-2xl font-bold text-boom-primary">Bs. {{ number_format($producto->precio, 0) }}</span>
                            <span class="text-sm text-gray-500">Stock: {{ $producto->stock }}</span>
                        </div>
                        
                        @if(auth()->check() && auth()->user()->id_rol == 3)
                            {{-- Vista para clientes: sin botón de pedido --}}
                            @if($producto->stock > 0)
                                <div class="w-full bg-gray-100 text-gray-600 font-bold py-2 px-4 rounded text-center">
                                    <i class="fas fa-eye mr-2"></i>
                                    Disponible
                                </div>
                            @else
                                <div class="w-full bg-gray-400 text-white font-bold py-2 px-4 rounded text-center">
                                    <i class="fas fa-times mr-2"></i>
                                    Sin Stock
                                </div>
                            @endif
                        @else
                            {{-- Vista para admin/empleados: con botón de pedido --}}
                            @if($producto->stock > 0)
                                <button onclick="seleccionarProducto('{{ $producto->nombre }}', {{ $producto->precio }}, '{{ strtolower($producto->categoria) }}')" 
                                        class="w-full bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Hacer Pedido
                                </button>
                            @else
                                <button disabled 
                                        class="w-full bg-gray-400 text-white font-bold py-2 px-4 rounded cursor-not-allowed">
                                    <i class="fas fa-times mr-2"></i>
                                    Sin Stock
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="bg-boom-cream-100 rounded-lg p-8">
                        <i class="fas fa-search text-6xl text-boom-text-medium mb-4"></i>
                        <h3 class="text-xl font-semibold text-boom-text-dark mb-2">
                            @if(($filtros['busqueda'] ?? '') || ($filtros['categoria'] ?? '') || ($filtros['precio_min'] ?? '') || ($filtros['precio_max'] ?? ''))
                                No se encontraron productos
                            @else
                                No hay productos disponibles
                            @endif
                        </h3>
                        <p class="text-boom-text-medium mb-4">
                            @if(($filtros['busqueda'] ?? '') || ($filtros['categoria'] ?? '') || ($filtros['precio_min'] ?? '') || ($filtros['precio_max'] ?? ''))
                                Intenta ajustar los filtros de búsqueda para encontrar más productos.
                            @else
                                Actualmente no tenemos productos en el catálogo.
                            @endif
                        </p>
                        @if(($filtros['busqueda'] ?? '') || ($filtros['categoria'] ?? '') || ($filtros['precio_min'] ?? '') || ($filtros['precio_max'] ?? ''))
                            <a href="{{ route('catalogo.index') }}" class="bg-boom-primary hover:bg-boom-primary-dark text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-times mr-2"></i>Limpiar filtros
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($productos->hasPages())
            <div class="mt-8">
                {{ $productos->links() }}
            </div>
        @endif


    </div>

    <!-- Modal para seleccionar cliente -->
    <div id="modalCliente" class="fixed inset-0 bg-black bg-opacity-30 hidden z-50" onclick="cerrarModalCliente()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
                <div class="bg-boom-primary text-white p-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>
                        Seleccionar Cliente
                    </h3>
                    <button onclick="cerrarModalCliente()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="p-4">
                    <div id="productoSeleccionado" class="bg-gray-50 p-3 rounded mb-4">
                        <!-- Información del producto seleccionado -->
                    </div>
                    
                    <form id="formPedido" method="POST" action="{{ route('catalogo.crear-pedido') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Seleccionar Cliente:
                            </label>
                            <select name="cliente_id" id="cliente_id" class="form-select block w-full rounded-md shadow-sm" required>
                                <option value="">Seleccione un cliente...</option>
                                @foreach(App\Models\Cliente::orderBy('nombre')->get() as $cliente)
                                    <option value="{{ $cliente->id }}">
                                        {{ $cliente->nombre }} {{ $cliente->apellido }} - {{ $cliente->ci_nit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Campos ocultos para el producto -->
                        <input type="hidden" name="producto_nombre" id="producto_nombre">
                        <input type="hidden" name="producto_precio" id="producto_precio">
                        <input type="hidden" name="categoria" id="producto_categoria">
                        
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="cerrarModalCliente()" 
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="bg-boom-primary hover:bg-boom-primary-dark text-white px-4 py-2 rounded">
                                Crear Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function seleccionarProducto(nombre, precio, categoria) {
            // Mostrar información del producto en el modal
            document.getElementById('productoSeleccionado').innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-tshirt text-boom-primary text-2xl mr-3"></i>
                    <div>
                        <div class="font-semibold text-boom-text-dark">${nombre}</div>
                        <div class="text-boom-primary font-bold">Bs. ${precio.toLocaleString()}</div>
                        <div class="text-sm text-gray-500 capitalize">${categoria}</div>
                    </div>
                </div>
            `;
            
            // Llenar campos ocultos
            document.getElementById('producto_nombre').value = nombre;
            document.getElementById('producto_precio').value = precio;
            document.getElementById('producto_categoria').value = categoria;
            
            // Mostrar modal
            document.getElementById('modalCliente').classList.remove('hidden');
        }
        
        function cerrarModalCliente() {
            document.getElementById('modalCliente').classList.add('hidden');
        }
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalCliente();
            }
        });

        // Búsqueda en tiempo real (opcional)
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="busqueda"]');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        // Auto-submit después de 1 segundo de inactividad
                        if (this.value.length >= 3 || this.value.length === 0) {
                            this.form.submit();
                        }
                    }, 1000);
                });
            }
        });
    </script>
    @endpush
@endsection
