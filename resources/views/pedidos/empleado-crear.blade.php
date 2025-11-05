@extends('layouts.app')

@section('content')
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                        <h2 class="text-xl sm:text-2xl font-bold text-boom-text-dark">Crear Pedido para Cliente</h2>
                        <a href="{{ route('pedidos.index') }}" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-semibold py-2 px-4 rounded-lg transition-colors duration-300 text-center sm:text-left">
                            <i class="fas fa-arrow-left mr-2"></i>Volver a Pedidos
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

                    <form action="{{ route('pedidos.empleado-store') }}" method="POST" id="pedidoEmpleadoForm">
                        @csrf
                        
                        <!-- Selección de Cliente -->
                        <div class="mb-4 sm:mb-6">
                            <label for="id_cliente" class="block text-sm font-medium text-boom-text-dark mb-2">
                                Cliente *
                            </label>
                            <select name="id_cliente" id="id_cliente" required
                                    class="w-full px-3 py-2 text-sm sm:text-base border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent">
                                <option value="">Seleccionar cliente...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ old('id_cliente') == $cliente->id ? 'selected' : '' }}
                                            data-telefono="{{ $cliente->telefono }}" data-ci="{{ $cliente->ci_nit }}">
                                        {{ $cliente->nombre }} {{ $cliente->apellido }} - CI: {{ $cliente->ci_nit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Información del Cliente Seleccionado -->
                        <div id="info-cliente" class="bg-boom-cream-100 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6" style="display: none;">
                            <h4 class="font-semibold text-boom-text-dark mb-2 text-sm sm:text-base">Información del Cliente</h4>
                            <div id="datos-cliente" class="text-sm"></div>
                        </div>

                        <!-- Selección de Producto por Categorías -->
                        <div class="mb-4 sm:mb-6">
                            <h3 class="text-base sm:text-lg font-semibold text-boom-text-dark mb-4 sm:mb-6">Seleccionar Producto</h3>
                            
                            @php
                                $productosPorCategoria = collect($productos)->groupBy('categoria');
                            @endphp
                            
                            @foreach($productosPorCategoria as $categoria => $productosCategoria)
                                <div class="mb-6 sm:mb-10">
                                    <!-- Etiqueta de Categoría Simple -->
                                    <div class="mb-4 sm:mb-6">
                                        <span class="inline-flex items-center px-3 sm:px-4 py-2 bg-boom-rose-dark text-white font-semibold rounded-lg shadow-sm text-sm sm:text-base">
                                            <i class="fas {{ $categoria == 'Formal' ? 'fa-user-tie' : ($categoria == 'Informal' ? 'fa-tshirt' : ($categoria == 'Deportivo' ? 'fa-running' : 'fa-tags')) }} mr-2"></i>
                                            {{ $categoria }}
                                        </span>
                                    </div>
                                    
                                    <!-- Productos de la Categoría -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                                        @foreach($productosCategoria as $index => $producto)
                                            @php
                                                $globalIndex = array_search($producto, $productos);
                                            @endphp
                                            <div class="bg-white border border-boom-cream-300 rounded-xl p-3 sm:p-5 cursor-pointer hover:shadow-lg hover:border-boom-rose-dark transition-all duration-300 producto-card" 
                                                 data-nombre="{{ $producto['nombre'] }}" 
                                                 data-precio="{{ $producto['precio'] }}" 
                                                 data-categoria="{{ $producto['categoria'] }}"
                                                 data-descripcion="{{ $producto['descripcion'] }}"
                                                 data-colores="{{ json_encode($producto['colores']) }}"
                                                 data-tallas="{{ json_encode($producto['tallas']) }}"
                                                 data-imagen="{{ asset($producto['imagen']) }}"
                                                 data-stock="{{ $producto['stock'] ?? 0 }}">
                                                
                                                <!-- Imagen de la prenda -->
                                                <div class="relative mb-3 sm:mb-4">
                                                    <img src="{{ asset($producto['imagen']) }}" 
                                                         alt="{{ $producto['nombre'] }}" 
                                                         class="w-full h-40 sm:h-48 object-cover rounded-lg">
                                                    
                                                    <!-- Botón de previsualización -->
                                                    <button type="button" onclick="mostrarPreview({{ $globalIndex }})" 
                                                            class="absolute top-1 sm:top-2 right-1 sm:right-2 bg-white bg-opacity-90 hover:bg-opacity-100 text-boom-text-dark p-1.5 sm:p-2 rounded-full shadow-md transition-all duration-300 text-xs sm:text-sm"
                                                            title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <!-- Radio button -->
                                                    <div class="absolute top-1 sm:top-2 left-1 sm:left-2">
                                                        <input type="radio" name="producto_seleccionado" value="{{ $globalIndex }}" 
                                                               id="producto_{{ $globalIndex }}" class="w-4 h-4 sm:w-5 sm:h-5 text-boom-rose-dark">
                                                    </div>
                                                </div>
                                                
                                                <!-- Información del producto -->
                                                <div class="space-y-1 sm:space-y-2">
                                                    <label for="producto_{{ $globalIndex }}" class="block font-semibold text-boom-text-dark cursor-pointer hover:text-boom-rose-dark transition-colors text-sm sm:text-base">
                                                        {{ $producto['nombre'] }}
                                                    </label>
                                                    
                                                    <p class="text-lg sm:text-xl font-bold text-boom-rose-dark">
                                                        Bs. {{ number_format($producto['precio'], 2) }}
                                                    </p>
                                                    
                                                    <!-- Stock disponible -->
                                                    @if(isset($producto['stock']))
                                                        <div class="flex items-center text-xs sm:text-sm {{ $producto['stock'] > 2 ? 'text-green-600' : ($producto['stock'] > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                                            <i class="fas fa-box mr-1 sm:mr-2"></i>
                                                            <span>{{ $producto['stock'] }} docenas ({{ $producto['stock'] * 12 }} unidades)</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Colores disponibles -->
                                                    <div class="text-xs sm:text-sm text-boom-text-medium">
                                                        <div class="flex flex-wrap gap-1 mt-1 sm:mt-2">
                                                            @foreach(array_slice($producto['colores'], 0, 3) as $color)
                                                                <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 bg-boom-cream-100 text-boom-text-dark rounded-full text-xs">
                                                                    {{ $color }}
                                                                </span>
                                                            @endforeach
                                                            @if(count($producto['colores']) > 3)
                                                                <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 bg-boom-cream-200 text-boom-text-medium rounded-full text-xs">
                                                                    +{{ count($producto['colores']) - 3 }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Tallas disponibles -->
                                                    <div class="text-xs sm:text-sm text-boom-text-medium">
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach(array_slice($producto['tallas'], 0, 4) as $talla)
                                                                <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 border border-boom-cream-300 text-boom-text-dark rounded text-xs">
                                                                    {{ $talla }}
                                                                </span>
                                                            @endforeach
                                                            @if(count($producto['tallas']) > 4)
                                                                <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 bg-boom-cream-200 text-boom-text-medium rounded text-xs">
                                                                    +{{ count($producto['tallas']) - 4 }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Campos ocultos para el producto seleccionado -->
                        <input type="hidden" name="producto_nombre" id="producto_nombre" value="{{ old('producto_nombre') }}">
                        <input type="hidden" name="producto_precio" id="producto_precio" value="{{ old('producto_precio') }}">
                        <input type="hidden" name="categoria" id="categoria" value="{{ old('categoria') }}">

                        <!-- Selección de Cantidad -->
                        <div class="mb-4 sm:mb-6" id="cantidad-section" style="display: none;">
                            <h3 class="text-base sm:text-lg font-semibold text-boom-text-dark mb-3 sm:mb-4">Cantidad (Por Docenas)</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 mb-3 sm:mb-4">
                                <p class="text-blue-800 text-xs sm:text-sm mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Venta por mayoreo:</strong> Los productos se venden por docenas (12 unidades mínimo)
                                </p>
                                <ul class="text-blue-700 text-xs sm:text-sm space-y-1">
                                    <li>• 1 docena = 12 unidades</li>
                                    <li>• 2 docenas = 24 unidades</li>
                                    <li>• 3 docenas = 36 unidades</li>
                                    <li>• Y así sucesivamente...</li>
                                </ul>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                                <label for="cantidad_docenas" class="block text-sm font-medium text-boom-text-dark">
                                    Número de docenas *
                                </label>
                                <div class="flex items-center justify-center sm:justify-start space-x-2">
                                    <button type="button" id="btn-menos" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-bold py-2 px-3 rounded-lg transition-colors duration-300 text-sm">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="cantidad_docenas" id="cantidad_docenas" min="1" value="1" max="1"
                                           class="w-16 sm:w-20 text-center px-2 sm:px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent text-sm sm:text-base"
                                           data-stock="0" data-max-docenas="1">
                                    <button type="button" id="btn-mas" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-bold py-2 px-3 rounded-lg transition-colors duration-300 text-sm">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <span class="text-xs sm:text-sm text-boom-text-medium text-center sm:text-left">
                                    = <span id="total-unidades">12</span> unidades
                                </span>
                            </div>
                        </div>

                        <!-- Detalles del Pedido -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                            <div>
                                <label for="descripcion_adicional" class="block text-sm font-medium text-boom-text-dark mb-2">
                                    Descripción Adicional
                                </label>
                                <textarea name="descripcion_adicional" id="descripcion_adicional" rows="4" 
                                          class="w-full px-3 py-2 text-sm sm:text-base border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent"
                                          placeholder="Especifica detalles como talla, color, modificaciones especiales, etc.">{{ old('descripcion_adicional') }}</textarea>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label for="direccion_entrega" class="block text-sm font-medium text-boom-text-dark mb-2">
                                        Dirección de Entrega
                                    </label>
                                    <textarea name="direccion_entrega" id="direccion_entrega" rows="2" 
                                              class="w-full px-3 py-2 text-sm sm:text-base border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent"
                                              placeholder="Dirección específica para la entrega (opcional)">{{ old('direccion_entrega') }}</textarea>
                                </div>
                                <div>
                                    <label for="telefono_contacto" class="block text-sm font-medium text-boom-text-dark mb-2">
                                        Teléfono de Contacto
                                    </label>
                                    <input type="tel" name="telefono_contacto" id="telefono_contacto"
                                           class="w-full px-3 py-2 text-sm sm:text-base border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent"
                                           placeholder="Teléfono alternativo (opcional)" value="{{ old('telefono_contacto') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Asignación de Operario (solo para administradores) -->
                        @if(Auth::user()->id_rol == 1 && count($operarios) > 0)
                            <div class="mb-4 sm:mb-6">
                                <label for="id_operario" class="block text-sm font-medium text-boom-text-dark mb-2">
                                    Asignar Operario (Opcional)
                                </label>
                                <select name="id_operario" id="id_operario"
                                        class="w-full px-3 py-2 text-sm sm:text-base border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent">
                                    <option value="">Sin asignar (se asignará después)</option>
                                    @foreach($operarios as $operario)
                                        <option value="{{ $operario->id_usuario }}" {{ old('id_operario') == $operario->id_usuario ? 'selected' : '' }}>
                                            {{ $operario->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs sm:text-sm text-boom-text-medium mt-1">
                                    Si asignas un operario, el pedido pasará directamente al estado "Asignado"
                                </p>
                            </div>
                        @endif

                        <!-- Resumen del Pedido -->
                        <div class="bg-boom-cream-100 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6" id="resumen-pedido" style="display: none;">
                            <h4 class="font-semibold text-boom-text-dark mb-2 text-sm sm:text-base">Resumen del Pedido</h4>
                            <div id="resumen-contenido" class="text-sm"></div>
                            <div class="border-t border-boom-cream-300 mt-2 pt-2">
                                <p class="font-bold text-boom-text-dark text-sm sm:text-base">Total: Bs. <span id="total-precio">0.00</span></p>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                            <a href="{{ route('pedidos.index') }}" 
                               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 sm:px-6 rounded-lg transition-colors duration-300 shadow-sm hover:shadow-md text-center text-sm sm:text-base">
                                Cancelar
                            </a>
                            <button type="submit" id="btn-crear-pedido" disabled
                                    class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-4 sm:px-6 rounded-lg transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                                <i class="fas fa-plus mr-2"></i>Crear Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Previsualización del Producto -->
    <div id="modalPreview" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" onclick="cerrarModalPreview()">
        <div class="flex items-center justify-center min-h-screen p-2 sm:p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[95vh] sm:max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                <div class="bg-boom-rose-dark text-white p-3 sm:p-4 flex justify-between items-center">
                    <h3 class="text-base sm:text-lg font-semibold" id="modalTitle">Previsualización del Producto</h3>
                    <button onclick="cerrarModalPreview()" class="text-white hover:text-gray-200 p-1">
                        <i class="fas fa-times text-lg sm:text-xl"></i>
                    </button>
                </div>
                
                <div class="p-3 sm:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Imagen del producto -->
                        <div class="flex justify-center">
                            <img id="modalImage" src="" alt="" class="w-full max-w-xs sm:max-w-sm h-auto rounded-lg" loading="lazy">
                        </div>
                        
                        <!-- Información del producto -->
                        <div class="space-y-3 sm:space-y-4">
                            <h4 class="text-lg sm:text-xl font-bold text-boom-text-dark" id="modalProductName"></h4>
                            <p class="text-sm sm:text-base text-boom-text-medium" id="modalProductDescription"></p>
                            
                            <div>
                                <span class="text-xs sm:text-sm text-boom-text-medium">Categoría:</span>
                                <span class="font-semibold text-boom-text-dark ml-2 text-sm sm:text-base" id="modalProductCategory"></span>
                            </div>
                            
                            <div>
                                <span class="text-xl sm:text-2xl font-bold text-boom-rose-dark" id="modalProductPrice"></span>
                            </div>
                            
                            <!-- Colores disponibles -->
                            <div>
                                <h5 class="font-semibold text-boom-text-dark mb-2 text-sm sm:text-base">Colores Disponibles:</h5>
                                <div id="modalProductColors" class="flex flex-wrap gap-1 sm:gap-2"></div>
                            </div>
                            
                            <!-- Tallas disponibles -->
                            <div>
                                <h5 class="font-semibold text-boom-text-dark mb-2 text-sm sm:text-base">Tallas Disponibles:</h5>
                                <div id="modalProductSizes" class="flex flex-wrap gap-1 sm:gap-2"></div>
                            </div>
                            
                            <!-- Información adicional -->
                            <div class="bg-boom-cream-100 rounded-lg p-2 sm:p-3 text-center">
                                <p class="text-xs sm:text-sm text-boom-text-medium">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Cierra esta ventana y selecciona el producto en la lista principal
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clienteSelect = document.getElementById('id_cliente');
            const infoCliente = document.getElementById('info-cliente');
            const datosCliente = document.getElementById('datos-cliente');
            const productoCards = document.querySelectorAll('.producto-card');
            const resumenPedido = document.getElementById('resumen-pedido');
            const resumenContenido = document.getElementById('resumen-contenido');
            const totalPrecio = document.getElementById('total-precio');
            const btnCrearPedido = document.getElementById('btn-crear-pedido');
            
            // Campos ocultos
            const productoNombre = document.getElementById('producto_nombre');
            const productoPrecio = document.getElementById('producto_precio');
            const categoria = document.getElementById('categoria');

            // Elementos de cantidad
            const cantidadSection = document.getElementById('cantidad-section');
            const cantidadDocenas = document.getElementById('cantidad_docenas');
            const totalUnidades = document.getElementById('total-unidades');
            const btnMenos = document.getElementById('btn-menos');
            const btnMas = document.getElementById('btn-mas');

            let clienteSeleccionado = false;
            let productoSeleccionado = false;

            // Manejar selección de cliente
            clienteSelect.addEventListener('change', function() {
                if (this.value) {
                    const option = this.options[this.selectedIndex];
                    const telefono = option.dataset.telefono;
                    const ci = option.dataset.ci;
                    
                    datosCliente.innerHTML = `
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4">
                            <p class="text-xs sm:text-sm"><strong>Nombre:</strong> ${option.text.split(' - CI:')[0]}</p>
                            <p class="text-xs sm:text-sm"><strong>CI/NIT:</strong> ${ci}</p>
                            <p class="text-xs sm:text-sm"><strong>Teléfono:</strong> ${telefono || 'No registrado'}</p>
                        </div>
                    `;
                    infoCliente.style.display = 'block';
                    clienteSeleccionado = true;
                } else {
                    infoCliente.style.display = 'none';
                    clienteSeleccionado = false;
                }
                actualizarBoton();
            });

            // Manejar selección de producto
            productoCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Si se hizo clic en el botón de preview, no seleccionar
                    if (e.target.closest('button')) {
                        return;
                    }
                    
                    // Remover selección anterior
                    productoCards.forEach(c => c.classList.remove('ring-2', 'ring-boom-rose-dark', 'bg-boom-rose-light', 'border-boom-rose-dark'));
                    
                    // Seleccionar producto actual
                    this.classList.add('ring-2', 'ring-boom-rose-dark', 'bg-boom-rose-light', 'border-boom-rose-dark');
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    
                    // Obtener datos del producto
                    const nombre = this.dataset.nombre;
                    const precio = parseFloat(this.dataset.precio);
                    const cat = this.dataset.categoria;
                    
                    // Actualizar campos ocultos
                    productoNombre.value = nombre;
                    productoPrecio.value = precio;
                    categoria.value = cat;
                    
                    // Mostrar sección de cantidad y resumen
                    cantidadSection.style.display = 'block';
                    
                    // Actualizar límites de stock
                    const stock = parseInt(card.dataset.stock) || 0;
                    actualizarLimitesStock(stock);
                    
                    actualizarResumen();
                    productoSeleccionado = true;
                    actualizarBoton();
                });
            });

            // Manejar botones de cantidad
            btnMas.addEventListener('click', function() {
                const maxDocenas = parseInt(cantidadDocenas.getAttribute('data-max-docenas')) || 1;
                const cantidadActual = parseInt(cantidadDocenas.value) || 1;
                const stock = parseInt(cantidadDocenas.getAttribute('data-stock')) || 0;
                
                if (cantidadActual < maxDocenas) {
                    cantidadDocenas.value = cantidadActual + 1;
                    actualizarCantidad();
                    actualizarResumen();
                    actualizarEstadoBotones();
                } else {
                    mostrarAlertaStock(stock, maxDocenas);
                }
            });

            btnMenos.addEventListener('click', function() {
                const cantidadActual = parseInt(cantidadDocenas.value) || 1;
                if (cantidadActual > 1) {
                    cantidadDocenas.value = cantidadActual - 1;
                    actualizarCantidad();
                    actualizarResumen();
                    actualizarEstadoBotones();
                }
            });

            cantidadDocenas.addEventListener('input', function() {
                const maxDocenas = parseInt(this.getAttribute('data-max-docenas')) || 1;
                const stock = parseInt(this.getAttribute('data-stock')) || 0;
                let cantidad = parseInt(this.value) || 1;
                
                // Validar límites
                if (cantidad < 1) {
                    cantidad = 1;
                } else if (cantidad > maxDocenas) {
                    cantidad = maxDocenas;
                    mostrarAlertaStock(stock, maxDocenas);
                }
                
                this.value = cantidad;
                actualizarCantidad();
                actualizarResumen();
                actualizarEstadoBotones();
            });

            function actualizarCantidad() {
                const docenas = parseInt(cantidadDocenas.value);
                const unidades = docenas * 12;
                totalUnidades.textContent = unidades;
            }

            function actualizarResumen() {
                if (clienteSeleccionado && productoSeleccionado) {
                    const clienteTexto = clienteSelect.options[clienteSelect.selectedIndex].text.split(' - CI:')[0];
                    const nombre = productoNombre.value;
                    const precio = parseFloat(productoPrecio.value);
                    const cat = categoria.value;
                    const docenas = parseInt(cantidadDocenas.value);
                    const unidades = docenas * 12;
                    const precioTotal = precio * docenas;
                    
                    // Obtener información adicional del producto seleccionado
                    const productoSeleccionadoCard = document.querySelector('.producto-card.bg-boom-rose-light');
                    let descripcionHtml = '';
                    let coloresHtml = '';
                    let tallasHtml = '';
                    
                    if (productoSeleccionadoCard) {
                        const descripcion = productoSeleccionadoCard.dataset.descripcion;
                        const colores = JSON.parse(productoSeleccionadoCard.dataset.colores);
                        const tallas = JSON.parse(productoSeleccionadoCard.dataset.tallas);
                        
                        descripcionHtml = `<p class="text-sm text-boom-text-medium mt-1">${descripcion}</p>`;
                        coloresHtml = colores.map(color => `<span class="inline-block bg-boom-cream-200 text-boom-text-dark px-2 py-1 rounded text-xs mr-1">${color}</span>`).join('');
                        tallasHtml = tallas.map(talla => `<span class="inline-block bg-boom-cream-100 text-boom-text-dark px-2 py-1 rounded text-xs mr-1 border">${talla}</span>`).join('');
                    }
                    
                    resumenContenido.innerHTML = `
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                            <div class="space-y-1 sm:space-y-2">
                                <p class="text-xs sm:text-sm"><strong>Cliente:</strong> ${clienteTexto}</p>
                                <p class="text-xs sm:text-sm"><strong>Producto:</strong> ${nombre}</p>
                                <p class="text-xs sm:text-sm"><strong>Categoría:</strong> ${cat}</p>
                                <p class="text-xs sm:text-sm"><strong>Cantidad:</strong> ${docenas} docena${docenas > 1 ? 's' : ''} (${unidades} unidades)</p>
                                ${descripcionHtml}
                            </div>
                            <div class="space-y-2 sm:space-y-3">
                                <p class="text-xs sm:text-sm"><strong>Precio por docena:</strong> Bs. ${precio.toFixed(2)}</p>
                                <div>
                                    <p class="text-xs sm:text-sm font-medium">Colores disponibles:</p>
                                    <div class="mt-1">${coloresHtml}</div>
                                </div>
                                <div>
                                    <p class="text-xs sm:text-sm font-medium">Tallas disponibles:</p>
                                    <div class="mt-1">${tallasHtml}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    totalPrecio.textContent = precioTotal.toFixed(2);
                    resumenPedido.style.display = 'block';
                }
            }

            function actualizarLimitesStock(stock) {
                // El stock ya está en docenas, no necesitamos dividir por 12
                const docentasMaximas = stock;
                const cantidadInput = document.getElementById('cantidad_docenas');
                const btnMas = document.getElementById('btn-mas');
                const btnMenos = document.getElementById('btn-menos');
                
                // Actualizar atributos del input
                cantidadInput.setAttribute('max', docentasMaximas);
                cantidadInput.setAttribute('data-stock', stock);
                cantidadInput.setAttribute('data-max-docenas', docentasMaximas);
                
                // Mostrar información de stock en la interfaz
                mostrarInfoStock(stock, docentasMaximas);
                
                // Si no hay stock disponible
                if (docentasMaximas < 1) {
                    cantidadInput.value = 0;
                    cantidadInput.disabled = true;
                    btnMas.disabled = true;
                    btnMenos.disabled = true;
                    
                    // Mostrar alerta
                    mostrarAlertaStock(stock, 0);
                } else {
                    // Resetear a 1 si el valor actual excede el máximo
                    const valorActual = parseInt(cantidadInput.value) || 1;
                    if (valorActual > docentasMaximas) {
                        cantidadInput.value = docentasMaximas;
                        mostrarAlertaStock(stock, docentasMaximas);
                    }
                    
                    cantidadInput.disabled = false;
                    btnMenos.disabled = false;
                    
                    // Actualizar estado del botón más
                    actualizarEstadoBotones();
                }
                
                actualizarCantidad();
            }
            
            function mostrarInfoStock(stock, docentasMaximas) {
                // Agregar información de stock después del label
                const label = document.querySelector('label[for="cantidad_docenas"]');
                let infoStock = document.getElementById('info-stock-cantidad');
                
                if (!infoStock) {
                    infoStock = document.createElement('div');
                    infoStock.id = 'info-stock-cantidad';
                    infoStock.className = 'text-sm mt-1';
                    label.parentNode.insertBefore(infoStock, label.nextSibling);
                }
                
                // Colores basados en docenas disponibles
                const colorClass = stock > 2 ? 'text-green-600' : (stock > 0 ? 'text-yellow-600' : 'text-red-600');
                const unidadesEquivalentes = stock * 12;
                
                if (docentasMaximas < 1) {
                    infoStock.innerHTML = `
                        <span class="text-red-600 font-medium">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Sin stock disponible
                        </span>
                    `;
                } else {
                    infoStock.innerHTML = `
                        <span class="${colorClass}">
                            <i class="fas fa-box mr-1"></i>
                            Stock disponible: ${stock} docenas (${unidadesEquivalentes} unidades)
                        </span>
                    `;
                }
            }
            
            function mostrarAlertaStock(stock, docentasMaximas) {
                const unidadesEquivalentes = stock * 12;
                const mensaje = docentasMaximas === 0 
                    ? `⚠️ Sin stock disponible`
                    : `⚠️ Stock limitado: Solo hay ${stock} docenas disponibles (${unidadesEquivalentes} unidades)`;
                
                // Crear o actualizar alerta
                let alerta = document.getElementById('alerta-stock-empleado');
                if (!alerta) {
                    alerta = document.createElement('div');
                    alerta.id = 'alerta-stock-empleado';
                    alerta.className = 'bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4';
                    document.getElementById('cantidad-section').insertBefore(alerta, document.getElementById('cantidad-section').firstChild);
                }
                
                alerta.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span>${mensaje}</span>
                    </div>
                `;
                
                // Auto-ocultar después de 5 segundos
                setTimeout(() => {
                    if (alerta && alerta.parentNode) {
                        alerta.remove();
                    }
                }, 5000);
            }
            
            function actualizarEstadoBotones() {
                const cantidadInput = document.getElementById('cantidad_docenas');
                const btnMas = document.getElementById('btn-mas');
                const btnMenos = document.getElementById('btn-menos');
                const cantidad = parseInt(cantidadInput.value) || 1;
                const maxDocenas = parseInt(cantidadInput.getAttribute('data-max-docenas')) || 1;
                
                // Botón menos: deshabilitar si cantidad es 1
                btnMenos.disabled = cantidad <= 1;
                btnMenos.classList.toggle('opacity-50', cantidad <= 1);
                btnMenos.classList.toggle('cursor-not-allowed', cantidad <= 1);
                
                // Botón más: deshabilitar si se alcanza el máximo
                btnMas.disabled = cantidad >= maxDocenas;
                btnMas.classList.toggle('opacity-50', cantidad >= maxDocenas);
                btnMas.classList.toggle('cursor-not-allowed', cantidad >= maxDocenas);
            }

            function actualizarBoton() {
                btnCrearPedido.disabled = !(clienteSeleccionado && productoSeleccionado);
            }

            // Validar formulario antes de enviar
            document.getElementById('pedidoEmpleadoForm').addEventListener('submit', function(e) {
                if (!clienteSeleccionado) {
                    e.preventDefault();
                    alert('Por favor selecciona un cliente');
                    return false;
                }
                
                if (!productoSeleccionado) {
                    e.preventDefault();
                    alert('Por favor selecciona un producto');
                    return false;
                }

                // Verificar que el token CSRF esté presente
                const csrfToken = document.querySelector('input[name="_token"]');
                if (!csrfToken || !csrfToken.value) {
                    e.preventDefault();
                    alert('Error de seguridad. Por favor recarga la página e intenta nuevamente.');
                    window.location.reload();
                    return false;
                }
            });

            // Actualizar resumen cuando cambie el cliente
            clienteSelect.addEventListener('change', function() {
                if (productoSeleccionado) {
                    actualizarResumen();
                }
            });

            // Función global para mostrar preview
            window.mostrarPreview = function(index) {
                const card = productoCards[index];
                const nombre = card.dataset.nombre;
                const precio = parseFloat(card.dataset.precio);
                const categoria = card.dataset.categoria;
                const descripcion = card.dataset.descripcion;
                const colores = JSON.parse(card.dataset.colores);
                const tallas = JSON.parse(card.dataset.tallas);
                const imagen = card.dataset.imagen;
                
                // Actualizar contenido del modal
                document.getElementById('modalTitle').textContent = `Previsualización: ${nombre}`;
                document.getElementById('modalImage').src = imagen;
                document.getElementById('modalImage').alt = nombre;
                document.getElementById('modalProductName').textContent = nombre;
                document.getElementById('modalProductDescription').textContent = descripcion;
                document.getElementById('modalProductCategory').textContent = categoria;
                document.getElementById('modalProductPrice').textContent = `Bs. ${precio.toFixed(2)}`;
                
                // Mostrar colores
                const coloresContainer = document.getElementById('modalProductColors');
                coloresContainer.innerHTML = colores.map(color => 
                    `<span class="bg-boom-cream-200 text-boom-text-dark px-3 py-2 rounded-lg font-medium">${color}</span>`
                ).join('');
                
                // Mostrar tallas
                const tallasContainer = document.getElementById('modalProductSizes');
                tallasContainer.innerHTML = tallas.map(talla => 
                    `<span class="bg-boom-cream-100 text-boom-text-dark px-3 py-2 rounded-lg border font-medium">${talla}</span>`
                ).join('');
                
                // Mostrar modal
                document.getElementById('modalPreview').classList.remove('hidden');
            }

            window.cerrarModalPreview = function() {
                document.getElementById('modalPreview').classList.add('hidden');
            }

            // Cerrar modal con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    cerrarModalPreview();
                }
            });
        });
    </script>
    @endpush
@endsection
