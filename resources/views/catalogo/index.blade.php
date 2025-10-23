<x-app-layout>
    <div class="p-2 sm:p-4 lg:p-6">
        <div class="flex justify-between items-center mb-4 lg:mb-6">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-tshirt mr-2"></i>
                <span class="hidden sm:inline">Catálogo de Productos</span>
                <span class="sm:hidden">Catálogo</span>
            </h1>
        </div>

        <!-- Filtros de categoría -->
        <div class="bg-white p-3 sm:p-4 rounded-lg shadow mb-4 lg:mb-6">
            <div class="flex flex-wrap gap-2 sm:gap-3">
                <button onclick="filtrarCategoria('todos')" 
                        class="categoria-btn bg-boom-primary text-white px-3 sm:px-4 py-2 rounded font-medium transition-colors duration-200 text-sm sm:text-base"
                        data-categoria="todos">
                    <i class="fas fa-th-large mr-1"></i>
                    Todos
                </button>
                @foreach($categorias as $categoria)
                    <button onclick="filtrarCategoria('{{ strtolower($categoria) }}')" 
                            class="categoria-btn bg-gray-200 text-gray-700 hover:bg-boom-primary hover:text-white px-3 sm:px-4 py-2 rounded font-medium transition-colors duration-200 text-sm sm:text-base"
                            data-categoria="{{ strtolower($categoria) }}">
                        @if(strtolower($categoria) == 'formal')
                            <i class="fas fa-user-tie mr-1"></i>
                        @elseif(strtolower($categoria) == 'informal')
                            <i class="fas fa-tshirt mr-1"></i>
                        @else
                            <i class="fas fa-tag mr-1"></i>
                        @endif
                        {{ $categoria }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Catálogo de productos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
            @forelse($productos as $producto)
                <div class="producto-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300" 
                     data-categoria="{{ strtolower($producto->categoria) }}">
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
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="bg-boom-cream-100 rounded-lg p-8">
                        <i class="fas fa-tshirt text-6xl text-boom-text-medium mb-4"></i>
                        <h3 class="text-xl font-semibold text-boom-text-dark mb-2">No hay productos disponibles</h3>
                        <p class="text-boom-text-medium">Actualmente no tenemos productos en el catálogo.</p>
                    </div>
                </div>
            @endforelse

            <!-- Vestido Casual -->
            <div class="producto-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300" data-categoria="vestidos">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=400&h=500&fit=crop" 
                         alt="Vestido Casual" class="w-full h-64 object-cover">
                    <div class="absolute top-2 right-2">
                        <span class="bg-pink-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            Casual
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-boom-text-dark mb-2">Vestido Casual Chic</h3>
                    <p class="text-gray-600 text-sm mb-3">Comodidad y estilo para el día a día. Versatilidad en cada ocasión.</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-2xl font-bold text-boom-primary">Bs. 380</span>
                        <span class="text-sm text-gray-500">Desde</span>
                    </div>
                    <button onclick="seleccionarProducto('Vestido Casual Chic', 380, 'vestidos')" 
                            class="w-full bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Hacer Pedido
                    </button>
                </div>
            </div>
        </div>
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
        function filtrarCategoria(categoria) {
            // Actualizar botones
            document.querySelectorAll('.categoria-btn').forEach(btn => {
                if (btn.dataset.categoria === categoria) {
                    btn.className = 'categoria-btn bg-boom-primary text-white px-4 py-2 rounded font-medium transition-colors duration-200';
                } else {
                    btn.className = 'categoria-btn bg-gray-200 text-gray-700 hover:bg-boom-primary hover:text-white px-4 py-2 rounded font-medium transition-colors duration-200';
                }
            });
            
            // Mostrar/ocultar productos
            document.querySelectorAll('.producto-card').forEach(card => {
                if (categoria === 'todos' || card.dataset.categoria === categoria) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
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
    </script>
    @endpush
</x-app-layout>