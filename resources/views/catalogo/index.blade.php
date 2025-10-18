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
                <button onclick="filtrarCategoria('trajes')" 
                        class="categoria-btn bg-gray-200 text-gray-700 hover:bg-boom-primary hover:text-white px-3 sm:px-4 py-2 rounded font-medium transition-colors duration-200 text-sm sm:text-base"
                        data-categoria="trajes">
                    <i class="fas fa-user-tie mr-1"></i>
                    Trajes
                </button>
                <button onclick="filtrarCategoria('vestidos')" 
                        class="categoria-btn bg-gray-200 text-gray-700 hover:bg-boom-primary hover:text-white px-3 sm:px-4 py-2 rounded font-medium transition-colors duration-200 text-sm sm:text-base"
                        data-categoria="vestidos">
                    <i class="fas fa-female mr-1"></i>
                    Vestidos
                </button>
                <button onclick="filtrarCategoria('blazers')" 
                        class="categoria-btn bg-gray-200 text-gray-700 hover:bg-boom-primary hover:text-white px-3 sm:px-4 py-2 rounded font-medium transition-colors duration-200 text-sm sm:text-base"
                        data-categoria="blazers">
                    <i class="fas fa-vest mr-1"></i>
                    Blazers
                </button>
                <button onclick="filtrarCategoria('camisas')" 
                        class="categoria-btn bg-gray-200 text-gray-700 hover:bg-boom-primary hover:text-white px-3 sm:px-4 py-2 rounded font-medium transition-colors duration-200 text-sm sm:text-base"
                        data-categoria="camisas">
                    <i class="fas fa-tshirt mr-1"></i>
                    Camisas
                </button>
            </div>
        </div>

        <!-- Catálogo de productos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
            <!-- Traje Ejecutivo -->
            <div class="producto-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300" data-categoria="trajes">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=500&fit=crop" 
                         alt="Traje Ejecutivo" class="w-full h-64 object-cover">
                    <div class="absolute top-2 right-2">
                        <span class="bg-blue-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            Ejecutivo
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-boom-text-dark mb-2">Traje Ejecutivo Clásico</h3>
                    <p class="text-gray-600 text-sm mb-3">Elegancia clásica para el mundo corporativo. Corte moderno y telas de alta calidad.</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-2xl font-bold text-boom-primary">Bs. 850</span>
                        <span class="text-sm text-gray-500">Desde</span>
                    </div>
                    <button onclick="seleccionarProducto('Traje Ejecutivo Clásico', 850, 'trajes')" 
                            class="w-full bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Hacer Pedido
                    </button>
                </div>
            </div>

            <!-- Vestido de Noche -->
            <div class="producto-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300" data-categoria="vestidos">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1566479179817-c0b5b4b4b1e5?w=400&h=500&fit=crop" 
                         alt="Vestido de Noche" class="w-full h-64 object-cover">
                    <div class="absolute top-2 right-2">
                        <span class="bg-purple-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            Elegante
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-boom-text-dark mb-2">Vestido de Noche Elegante</h3>
                    <p class="text-gray-600 text-sm mb-3">Sofisticación para eventos especiales. Diseños únicos y acabados impecables.</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-2xl font-bold text-boom-primary">Bs. 650</span>
                        <span class="text-sm text-gray-500">Desde</span>
                    </div>
                    <button onclick="seleccionarProducto('Vestido de Noche Elegante', 650, 'vestidos')" 
                            class="w-full bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Hacer Pedido
                    </button>
                </div>
            </div>

            <!-- Blazer Moderno -->
            <div class="producto-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300" data-categoria="blazers">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=400&h=500&fit=crop" 
                         alt="Blazer Moderno" class="w-full h-64 object-cover">
                    <div class="absolute top-2 right-2">
                        <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            Moderno
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-boom-text-dark mb-2">Blazer Moderno</h3>
                    <p class="text-gray-600 text-sm mb-3">Estilo contemporáneo con toque clásico. Perfecto para cualquier ocasión.</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-2xl font-bold text-boom-primary">Bs. 450</span>
                        <span class="text-sm text-gray-500">Desde</span>
                    </div>
                    <button onclick="seleccionarProducto('Blazer Moderno', 450, 'blazers')" 
                            class="w-full bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Hacer Pedido
                    </button>
                </div>
            </div>

            <!-- Camisa Formal -->
            <div class="producto-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300" data-categoria="camisas">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=400&h=500&fit=crop" 
                         alt="Camisa Formal" class="w-full h-64 object-cover">
                    <div class="absolute top-2 right-2">
                        <span class="bg-indigo-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            Formal
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-boom-text-dark mb-2">Camisa Formal Premium</h3>
                    <p class="text-gray-600 text-sm mb-3">Calidad superior en cada detalle. Comodidad y elegancia en una sola prenda.</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-2xl font-bold text-boom-primary">Bs. 280</span>
                        <span class="text-sm text-gray-500">Desde</span>
                    </div>
                    <button onclick="seleccionarProducto('Camisa Formal Premium', 280, 'camisas')" 
                            class="w-full bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Hacer Pedido
                    </button>
                </div>
            </div>

            <!-- Traje de Gala -->
            <div class="producto-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300" data-categoria="trajes">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?w=400&h=500&fit=crop" 
                         alt="Traje de Gala" class="w-full h-64 object-cover">
                    <div class="absolute top-2 right-2">
                        <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            Premium
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-boom-text-dark mb-2">Traje de Gala Premium</h3>
                    <p class="text-gray-600 text-sm mb-3">Para ocasiones especiales. Máxima elegancia y distinción en cada detalle.</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-2xl font-bold text-boom-primary">Bs. 1,200</span>
                        <span class="text-sm text-gray-500">Desde</span>
                    </div>
                    <button onclick="seleccionarProducto('Traje de Gala Premium', 1200, 'trajes')" 
                            class="w-full bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Hacer Pedido
                    </button>
                </div>
            </div>

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