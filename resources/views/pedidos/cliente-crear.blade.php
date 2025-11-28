@extends('layouts.app')

@section('content')
    <div class="p-3 sm:p-4 lg:p-8 max-w-7xl mx-auto">
        <div class="flex flex-col space-y-3 sm:flex-row sm:justify-between sm:items-center sm:space-y-0 mb-4 sm:mb-6">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-boom-text-dark text-center sm:text-left">
                <i class="fas fa-shopping-cart mr-2"></i>
                @if(Auth::user()->id_rol == 2)
                    Crear Pedido Personal
                @else
                    Crear Mi Pedido
                @endif
            </h1>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ route('catalogo.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors duration-300 text-center text-sm sm:text-base">
                    <i class="fas fa-images mr-2"></i>Ver Catálogo
                </a>
                <a href="{{ route('pedidos.mis-pedidos') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors duration-300 text-center text-sm sm:text-base">
                    <i class="fas fa-list mr-2"></i>Mis Pedidos
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">¡Oops! Hay algunos errores:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Información del Cliente - Optimizada para móvil -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
            <h3 class="text-base sm:text-lg font-semibold text-blue-800 mb-2">
                <i class="fas fa-user-check mr-2"></i>
                Información del Pedido
            </h3>
            <div class="flex items-center">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-500 rounded-full flex items-center justify-center text-white text-base sm:text-lg font-bold mr-3 sm:mr-4 flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->nombre, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="font-semibold text-blue-800 text-sm sm:text-base truncate">
                        {{ Auth::user()->nombre }} {{ Auth::user()->apellido ?? '' }}
                    </div>
                    <div class="text-xs sm:text-sm text-blue-600 truncate">
                        @if(Auth::user()->id_rol == 2)
                            Pedido Personal - Empleado
                        @else
                            Cliente - {{ Auth::user()->email }}
                        @endif
                    </div>
                </div>
            </div>
            <p class="text-xs sm:text-sm text-blue-700 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                Selecciona múltiples productos y cantidades para crear tu pedido personalizado.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-3 sm:p-6 bg-white border-b border-gray-200">

                <form action="{{ route('pedidos.cliente-store') }}" method="POST" id="pedidoForm">
                    @csrf
                    
                    <!-- Selección Múltiple de Productos - Optimizada para móvil -->
                    <div class="mb-4 sm:mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                            <h3 class="text-lg sm:text-xl font-semibold text-boom-text-dark">
                                <i class="fas fa-tshirt mr-2"></i>
                                Seleccionar Productos
                            </h3>
                            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                                <span class="text-xs sm:text-sm text-boom-text-medium bg-blue-100 px-2 sm:px-3 py-1 rounded-full text-center">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Múltiples productos disponibles
                                </span>
                                <button type="button" id="limpiar-seleccion" class="text-xs sm:text-sm text-boom-rose-dark hover:text-boom-rose-light transition-colors bg-red-100 px-2 sm:px-3 py-1 rounded-full text-center">
                                    <i class="fas fa-times mr-1"></i>Limpiar selección
                                </button>
                            </div>
                        </div>
                            
                        @php
                            $productosPorCategoria = collect($productos)->groupBy('categoria');
                        @endphp
                        
                        @foreach($productosPorCategoria as $categoria => $productosCategoria)
                            <div class="mb-6 sm:mb-8 bg-gray-50 rounded-lg p-3 sm:p-6">
                                <!-- Header de Categoría Profesional - Optimizado para móvil -->
                                <div class="flex items-center justify-between mb-4 sm:mb-6 pb-2 sm:pb-3 border-b border-gray-200">
                                    <h4 class="text-base sm:text-lg font-bold text-boom-text-dark flex items-center">
                                        @if($categoria == 'Formal')
                                            <i class="fas fa-user-tie text-blue-600 mr-2 sm:mr-3"></i>
                                        @elseif($categoria == 'Informal')
                                            <i class="fas fa-tshirt text-green-600 mr-2 sm:mr-3"></i>
                                        @else
                                            <i class="fas fa-tag text-purple-600 mr-2 sm:mr-3"></i>
                                        @endif
                                        {{ $categoria }}
                                    </h4>
                                    <span class="bg-boom-cream-300 text-boom-text-dark px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium">
                                        {{ count($productosCategoria) }} producto{{ count($productosCategoria) > 1 ? 's' : '' }}
                                    </span>
                                </div>
                                
                                <!-- Grid de Productos Profesional - Optimizado para móvil -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                                        @foreach($productosCategoria as $index => $producto)
                                            @php
                                                $globalIndex = array_search($producto, $productos);
                                            @endphp
                                            <div class="bg-white border-2 border-gray-200 rounded-lg shadow-md hover:shadow-xl hover:border-boom-rose-dark transition-all duration-300 producto-card overflow-hidden" 
                                                 data-id="{{ $producto['id'] }}"
                                                 data-nombre="{{ $producto['nombre'] }}" 
                                                 data-precio="{{ $producto['precio'] }}" 
                                                 data-categoria="{{ $producto['categoria'] }}"
                                                 data-descripcion="{{ $producto['descripcion'] }}"
                                                 data-colores="{{ json_encode($producto['colores']) }}"
                                                 data-tallas="{{ json_encode($producto['tallas']) }}"
                                                 data-imagen="{{ asset($producto['imagen']) }}"
                                                 data-stock="{{ $producto['stock'] ?? 0 }}">
                                                
                                                <!-- Imagen de la prenda - Optimizada para móvil -->
                                                <div class="relative">
                                                    @if($producto['imagen'] && file_exists(public_path($producto['imagen'])))
                                                        <img src="{{ asset($producto['imagen']) }}" 
                                                             alt="{{ $producto['nombre'] }}" 
                                                             class="w-full h-36 sm:h-48 object-cover">
                                                    @else
                                                        <div class="w-full h-36 sm:h-48 bg-boom-cream-200 flex items-center justify-center">
                                                            <i class="fas fa-tshirt text-2xl sm:text-4xl text-boom-text-medium"></i>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Botón de previsualización - Optimizado para móvil -->
                                                    <button type="button" onclick="mostrarPreview({{ $globalIndex }})" 
                                                            class="absolute top-1 sm:top-2 right-1 sm:right-2 bg-white bg-opacity-90 hover:bg-opacity-100 text-boom-text-dark p-1.5 sm:p-2 rounded-full shadow-md transition-all duration-300"
                                                            title="Ver detalles">
                                                        <i class="fas fa-eye text-sm sm:text-base"></i>
                                                    </button>
                                                    
                                                    <!-- Checkbox para selección múltiple -->
                                                    <div class="absolute top-2 left-2">
                                                        <input type="checkbox" name="productos_seleccionados[]" value="{{ $globalIndex }}" 
                                                               id="producto_{{ $globalIndex }}" class="w-5 h-5 text-boom-rose-dark rounded">
                                                    </div>
                                                    
                                                    <!-- Contador de cantidad -->
                                                    <div class="absolute bottom-2 right-2 bg-boom-rose-dark text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold cantidad-badge" 
                                                         id="badge_{{ $globalIndex }}" style="display: none;">
                                                        <span class="cantidad-numero">1</span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Información del producto -->
                                                <div class="p-4 space-y-3">
                                                    <div>
                                                        <label for="producto_{{ $globalIndex }}" class="block text-lg font-bold text-boom-text-dark cursor-pointer hover:text-boom-rose-dark transition-colors">
                                                            {{ $producto['nombre'] }}
                                                        </label>
                                                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($producto['descripcion'] ?? '', 60) }}</p>
                                                    </div>
                                                    
                                                    <!-- Precio destacado -->
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-2xl font-bold text-boom-rose-dark">
                                                            Bs. {{ number_format($producto['precio'], 0) }}
                                                        </span>
                                                        <span class="text-sm text-gray-500">
                                                            Stock: {{ $producto['stock'] ?? 0 }}
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Colores disponibles -->
                                                    @if(!empty($producto['colores']))
                                                        <div>
                                                            <p class="text-xs text-gray-500 mb-1">Colores:</p>
                                                            <div class="flex flex-wrap gap-1">
                                                                @foreach(array_slice($producto['colores'], 0, 3) as $color)
                                                                    <span class="text-xs bg-boom-cream-400 text-boom-text-dark border border-boom-cream-500 px-2 py-1 rounded font-medium">{{ $color }}</span>
                                                                @endforeach
                                                                @if(count($producto['colores']) > 3)
                                                                    <span class="text-xs bg-boom-cream-400 text-boom-text-dark border border-boom-cream-500 px-2 py-1 rounded font-medium">+{{ count($producto['colores']) - 3 }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Controles de cantidad (ocultos inicialmente) -->
                                                    <div class="cantidad-controls mt-2" id="controls_{{ $globalIndex }}" style="display: none;">
                                                        <div class="flex items-center justify-between bg-boom-cream-100 rounded-lg p-2">
                                                            <span class="text-sm font-medium text-boom-text-dark">Docenas:</span>
                                                            <div class="flex items-center space-x-2">
                                                                <button type="button" class="btn-menos bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark w-8 h-8 rounded-full flex items-center justify-center transition-colors" 
                                                                        data-producto="{{ $globalIndex }}">
                                                                    <i class="fas fa-minus text-xs"></i>
                                                                </button>
                                                                <input type="number" class="cantidad-input w-16 text-center border border-boom-cream-300 rounded px-2 py-1 text-sm" 
                                                                       value="1" min="1" max="{{ $producto['stock'] ?? 0 }}" 
                                                                       data-producto="{{ $globalIndex }}" 
                                                                       data-stock="{{ $producto['stock'] ?? 0 }}"
                                                                       data-max-docenas="{{ $producto['stock'] ?? 0 }}">
                                                                <button type="button" class="btn-mas bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark w-8 h-8 rounded-full flex items-center justify-center transition-colors" 
                                                                        data-producto="{{ $globalIndex }}"
                                                                        data-max-docenas="{{ $producto['stock'] ?? 0 }}">
                                                                    <i class="fas fa-plus text-xs"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <!-- Indicador de stock disponible -->
                                                        <div class="mt-1 text-xs text-center">
                                                            <span class="text-gray-600">Stock: {{ $producto['stock'] ?? 0 }} docenas</span>
                                                            <span class="text-boom-rose-dark font-medium">({{ ($producto['stock'] ?? 0) * 12 }} unidades)</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <p class="text-xl font-bold text-boom-rose-dark">
                                                        Bs. {{ number_format($producto['precio'], 2) }}
                                                    </p>
                                                    
                                                    <!-- Stock disponible -->
                                                    @if(isset($producto['stock']))
                                                        <div class="flex items-center text-sm {{ $producto['stock'] > 10 ? 'text-green-600' : ($producto['stock'] > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                                            <i class="fas fa-box mr-2"></i>
                                                            <span>{{ $producto['stock'] }} disponibles</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Colores disponibles -->
                                                    <div class="text-sm text-boom-text-medium">
                                                        <div class="flex flex-wrap gap-1 mt-2">
                                                            @foreach(array_slice($producto['colores'], 0, 4) as $color)
                                                                <span class="px-2 py-1 bg-boom-cream-100 text-boom-text-dark rounded-full text-xs">
                                                                    {{ $color }}
                                                                </span>
                                                            @endforeach
                                                            @if(count($producto['colores']) > 4)
                                                                <span class="px-2 py-1 bg-boom-cream-200 text-boom-text-medium rounded-full text-xs">
                                                                    +{{ count($producto['colores']) - 4 }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Tallas disponibles -->
                                                    <div class="text-sm text-boom-text-medium">
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach($producto['tallas'] as $talla)
                                                                <span class="px-2 py-1 border border-boom-cream-300 text-boom-text-dark rounded text-xs">
                                                                    {{ $talla }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Campos ocultos para los productos seleccionados -->
                        <input type="hidden" name="productos_data" id="productos_data" value="">
                        
                        <!-- Carrito de productos seleccionados -->
                        <div class="mb-6" id="carrito-productos" style="display: none;">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-shopping-cart mr-2"></i>Productos Seleccionados
                            </h3>
                            <div class="bg-boom-cream-100 rounded-lg p-4">
                                <div id="lista-productos-seleccionados" class="space-y-3">
                                    <!-- Los productos se agregarán dinámicamente aquí -->
                                </div>
                                <div class="border-t border-boom-cream-300 mt-4 pt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-boom-text-dark">Total del Pedido:</span>
                                        <span class="text-xl font-bold text-black" id="total-carrito">Bs. 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información sobre venta por docenas -->
                        <div class="mb-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-blue-800 text-sm mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Venta por mayoreo:</strong> Los productos se venden por docenas (12 unidades mínimo)
                                </p>
                                <ul class="text-blue-700 text-sm space-y-1">
                                    <li>• 1 docena = 12 unidades</li>
                                    <li>• 2 docenas = 24 unidades</li>
                                    <li>• 3 docenas = 36 unidades</li>
                                    <li>• Puedes seleccionar múltiples productos y cantidades diferentes</li>
                                    <li>• <strong>El stock se actualiza automáticamente</strong> al crear el pedido</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Alerta de Stock -->
                        <div id="alerta-stock" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4" style="display: none;">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                <div>
                                    <h4 class="font-semibold text-yellow-800">Atención: Stock Limitado</h4>
                                    <p class="text-yellow-700 text-sm mt-1" id="mensaje-stock"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción Adicional -->
                        <div class="mb-6">
                            <label for="descripcion_adicional" class="block text-sm font-medium text-boom-text-dark mb-2">
                                Descripción Adicional (Opcional)
                            </label>
                            <textarea name="descripcion_adicional" id="descripcion_adicional" rows="3" 
                                      class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent"
                                      placeholder="Especifica detalles adicionales como talla, color, modificaciones especiales, etc.">{{ old('descripcion_adicional') }}</textarea>
                        </div>

                        <!-- Información de Contacto y Entrega -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="direccion_entrega" class="block text-sm font-medium text-boom-text-dark mb-2">
                                    Dirección de Entrega *
                                </label>
                                <textarea name="direccion_entrega" id="direccion_entrega" rows="3" required
                                          class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent"
                                          placeholder="Ingresa la dirección completa donde deseas recibir tu pedido">{{ old('direccion_entrega') }}</textarea>
                            </div>
                            <div>
                                <label for="telefono_contacto" class="block text-sm font-medium text-boom-text-dark mb-2">
                                    Teléfono de Contacto *
                                </label>
                                <input type="tel" name="telefono_contacto" id="telefono_contacto" required
                                       class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent"
                                       placeholder="Ej: 70123456" value="{{ old('telefono_contacto') }}">
                            </div>
                        </div>

                        <!-- Resumen del Pedido -->
                        <div class="bg-boom-cream-100 rounded-lg p-4 mb-6" id="resumen-pedido" style="display: none;">
                            <h4 class="font-semibold text-boom-text-dark mb-2">Resumen del Pedido</h4>
                            <div id="resumen-contenido"></div>
                            <div class="border-t border-boom-cream-300 mt-2 pt-2">
                                <p class="font-bold text-boom-text-dark">Total: Bs. <span id="total-precio">0.00</span></p>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('pedidos.mis-pedidos') }}" 
                               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300 shadow-sm hover:shadow-md">
                                Cancelar
                            </a>
                            <button type="submit" id="btn-crear-pedido" disabled
                                    class="bg-boom-rose-dark hover:bg-boom-rose-light text-black font-semibold py-2 px-6 rounded-lg transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                Crear Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Previsualización del Producto -->
    <div id="modalPreview" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" onclick="cerrarModalPreview()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                <div class="bg-boom-rose-dark text-white p-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold" id="modalTitle">Previsualización del Producto</h3>
                    <button onclick="cerrarModalPreview()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Imagen del producto -->
                        <div class="flex justify-center">
                            <img id="modalImage" src="" alt="" class="w-full max-w-sm h-auto" loading="lazy">
                        </div>
                        
                        <!-- Información del producto -->
                        <div>
                            <h4 class="text-xl font-bold text-boom-text-dark mb-2" id="modalProductName"></h4>
                            <p class="text-boom-text-medium mb-4" id="modalProductDescription"></p>
                            
                            <div class="mb-4">
                                <span class="text-sm text-boom-text-medium">Categoría:</span>
                                <span class="font-semibold text-boom-text-dark ml-2" id="modalProductCategory"></span>
                            </div>
                            
                            <div class="mb-4">
                                <span class="text-2xl font-bold text-boom-rose-dark" id="modalProductPrice"></span>
                            </div>
                            
                            <!-- Colores disponibles -->
                            <div class="mb-4">
                                <h5 class="font-semibold text-boom-text-dark mb-2">Colores Disponibles:</h5>
                                <div id="modalProductColors" class="flex flex-wrap gap-2"></div>
                            </div>
                            
                            <!-- Tallas disponibles -->
                            <div class="mb-6">
                                <h5 class="font-semibold text-boom-text-dark mb-2">Tallas Disponibles:</h5>
                                <div id="modalProductSizes" class="flex flex-wrap gap-2"></div>
                            </div>
                            
                            <!-- Información adicional -->
                            <div class="bg-boom-cream-100 rounded-lg p-3 text-center">
                                <p class="text-sm text-boom-text-medium">
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
            // Cache de elementos DOM para mejor rendimiento
            const elements = {
                productoCards: document.querySelectorAll('.producto-card'),
                carritoProductos: document.getElementById('carrito-productos'),
                listaProductosSeleccionados: document.getElementById('lista-productos-seleccionados'),
                totalCarrito: document.getElementById('total-carrito'),
                btnCrearPedido: document.getElementById('btn-crear-pedido'),
                productosData: document.getElementById('productos_data'),
                limpiarSeleccion: document.getElementById('limpiar-seleccion')
            };

            // Array para almacenar productos seleccionados
            let productosSeleccionados = [];

            // Manejar selección de productos con checkbox
            console.log('Inicializando selección múltiple...');
            console.log('Productos encontrados:', elements.productoCards.length);
            
            elements.productoCards.forEach((card, cardIndex) => {
                const checkbox = card.querySelector('input[type="checkbox"]');
                
                if (!checkbox) {
                    console.error('No se encontró checkbox en la tarjeta', cardIndex);
                    return;
                }
                
                const productoIndex = checkbox.value;
                console.log(`Configurando producto ${productoIndex} en tarjeta ${cardIndex}`);
                
                // Evento para el checkbox
                checkbox.addEventListener('change', function() {
                    console.log(`Checkbox ${productoIndex} cambió a:`, this.checked);
                    if (this.checked) {
                        agregarProducto(productoIndex, card);
                    } else {
                        removerProducto(productoIndex, card);
                    }
                });
                
                // Evento para hacer clic en la tarjeta (toggle checkbox)
                card.addEventListener('click', function(e) {
                    // Si se hizo clic en un botón o control, no hacer toggle
                    if (e.target.closest('button') || e.target.closest('input') || e.target.closest('.cantidad-controls')) {
                        console.log('Click ignorado en control');
                        return;
                    }
                    
                    console.log(`Click en tarjeta ${productoIndex}`);
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });

            // Manejar controles de cantidad
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-mas')) {
                    const productoIndex = e.target.closest('.btn-mas').dataset.producto;
                    cambiarCantidad(productoIndex, 1);
                } else if (e.target.closest('.btn-menos')) {
                    const productoIndex = e.target.closest('.btn-menos').dataset.producto;
                    cambiarCantidad(productoIndex, -1);
                }
            });

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('cantidad-input')) {
                    const productoIndex = e.target.dataset.producto;
                    const cantidad = Math.max(1, parseInt(e.target.value) || 1);
                    e.target.value = cantidad;
                    actualizarCantidadProducto(productoIndex, cantidad);
                }
            });

            // Limpiar selección
            elements.limpiarSeleccion.addEventListener('click', function() {
                limpiarTodaSeleccion();
            });

            // Funciones para manejar productos
            function agregarProducto(index, card) {
                console.log('Agregando producto:', index);
                
                const stock = parseInt(card.dataset.stock);
                
                // Verificar si hay stock disponible (mínimo 1 docena)
                if (stock < 1) {
                    alert(`Stock insuficiente para "${card.dataset.nombre}". Disponible: ${stock} docenas`);
                    return;
                }
                
                const producto = {
                    index: index,
                    id: parseInt(card.dataset.id), // Añadir ID para la base de datos
                    nombre: card.dataset.nombre,
                    precio: parseFloat(card.dataset.precio),
                    categoria: card.dataset.categoria,
                    descripcion: card.dataset.descripcion,
                    colores: JSON.parse(card.dataset.colores),
                    tallas: JSON.parse(card.dataset.tallas),
                    imagen: card.dataset.imagen,
                    stock: stock,
                    cantidad: 1
                };

                productosSeleccionados.push(producto);
                console.log('Productos seleccionados:', productosSeleccionados.length);
                
                // Actualizar UI
                card.classList.add('ring-2', 'ring-boom-rose-dark', 'bg-boom-rose-light', 'border-boom-rose-dark');
                
                const controls = document.getElementById(`controls_${index}`);
                const badge = document.getElementById(`badge_${index}`);
                
                if (controls) controls.style.display = 'block';
                if (badge) badge.style.display = 'flex';
                
                actualizarCarrito();
                elements.btnCrearPedido.disabled = false;
            }

            function removerProducto(index, card) {
                productosSeleccionados = productosSeleccionados.filter(p => p.index != index);
                
                // Actualizar UI
                card.classList.remove('ring-2', 'ring-boom-rose-dark', 'bg-boom-rose-light', 'border-boom-rose-dark');
                document.getElementById(`controls_${index}`).style.display = 'none';
                document.getElementById(`badge_${index}`).style.display = 'none';
                
                actualizarCarrito();
                
                if (productosSeleccionados.length === 0) {
                    elements.btnCrearPedido.disabled = true;
                }
            }

            function cambiarCantidad(index, cambio) {
                const input = document.querySelector(`input[data-producto="${index}"]`);
                const producto = productosSeleccionados.find(p => p.index == index);
                
                if (!producto) return;
                
                const cantidadActual = parseInt(input.value) || 1;
                const nuevaCantidad = Math.max(1, cantidadActual + cambio);
                const docentasMaximas = producto.stock; // El stock ya está en docenas
                
                // Validar que no exceda el stock disponible
                if (nuevaCantidad > docentasMaximas) {
                    // Mostrar alerta más informativa
                    mostrarAlertaStock(producto.nombre, producto.stock, docentasMaximas);
                    return;
                }
                
                // Actualizar el input y el producto
                input.value = nuevaCantidad;
                input.setAttribute('max', docentasMaximas);
                actualizarCantidadProducto(index, nuevaCantidad);
                
                // Actualizar estado de botones
                actualizarEstadoBotones(index, nuevaCantidad, docentasMaximas);
            }

            function mostrarAlertaStock(nombreProducto, stockTotal, docentasMaximas) {
                const alertaDiv = document.getElementById('alerta-stock');
                const mensajeSpan = document.getElementById('mensaje-stock');
                
                mensajeSpan.innerHTML = `
                    <strong>${nombreProducto}</strong><br>
                    Stock disponible: ${stockTotal} docenas (${stockTotal * 12} unidades)<br>
                    Máximo permitido: ${docentasMaximas} docena${docentasMaximas !== 1 ? 's' : ''} (${docentasMaximas * 12} unidades)
                `;
                
                alertaDiv.style.display = 'block';
                
                // Ocultar alerta después de 5 segundos
                setTimeout(() => {
                    alertaDiv.style.display = 'none';
                }, 5000);
            }

            function actualizarEstadoBotones(index, cantidadActual, docentasMaximas) {
                const btnMas = document.querySelector(`.btn-mas[data-producto="${index}"]`);
                const btnMenos = document.querySelector(`.btn-menos[data-producto="${index}"]`);
                
                // Deshabilitar botón + si se alcanzó el máximo
                if (btnMas) {
                    if (cantidadActual >= docentasMaximas) {
                        btnMas.disabled = true;
                        btnMas.classList.add('opacity-50', 'cursor-not-allowed');
                        btnMas.classList.remove('hover:bg-boom-cream-300');
                    } else {
                        btnMas.disabled = false;
                        btnMas.classList.remove('opacity-50', 'cursor-not-allowed');
                        btnMas.classList.add('hover:bg-boom-cream-300');
                    }
                }
                
                // Deshabilitar botón - si está en el mínimo
                if (btnMenos) {
                    if (cantidadActual <= 1) {
                        btnMenos.disabled = true;
                        btnMenos.classList.add('opacity-50', 'cursor-not-allowed');
                        btnMenos.classList.remove('hover:bg-boom-cream-300');
                    } else {
                        btnMenos.disabled = false;
                        btnMenos.classList.remove('opacity-50', 'cursor-not-allowed');
                        btnMenos.classList.add('hover:bg-boom-cream-300');
                    }
                }
            }

            function actualizarCantidadProducto(index, cantidad) {
                const producto = productosSeleccionados.find(p => p.index == index);
                if (producto) {
                    // Validar stock antes de actualizar (el stock ya está en docenas)
                    if (cantidad > producto.stock) {
                        const docentasMaximas = producto.stock;
                        cantidad = docentasMaximas;
                        document.querySelector(`input[data-producto="${index}"]`).value = cantidad;
                    }
                    
                    producto.cantidad = cantidad;
                    document.querySelector(`#badge_${index} .cantidad-numero`).textContent = cantidad;
                    actualizarCarrito();
                }
            }

            function actualizarCarrito() {
                if (productosSeleccionados.length === 0) {
                    elements.carritoProductos.style.display = 'none';
                    return;
                }

                elements.carritoProductos.style.display = 'block';
                
                let html = '';
                let totalGeneral = 0;

                productosSeleccionados.forEach(producto => {
                    const subtotal = producto.precio * producto.cantidad;
                    const unidades = producto.cantidad * 12;
                    const stockRestante = producto.stock - unidades;
                    const stockColor = stockRestante > 10 ? 'text-green-600' : (stockRestante > 0 ? 'text-yellow-600' : 'text-red-600');
                    totalGeneral += subtotal;

                    html += `
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-boom-cream-300">
                            <div class="flex-1">
                                <h4 class="font-semibold text-boom-text-dark">${producto.nombre}</h4>
                                <p class="text-sm text-boom-text-medium">${producto.categoria}</p>
                                <p class="text-sm text-boom-text-medium">${producto.cantidad} docena${producto.cantidad > 1 ? 's' : ''} (${unidades} unidades)</p>
                                <p class="text-xs ${stockColor}">
                                    <i class="fas fa-box mr-1"></i>Quedarán ${stockRestante} disponibles
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-boom-rose-dark">Bs. ${subtotal.toFixed(2)}</p>
                                <p class="text-xs text-boom-text-medium">Bs. ${producto.precio.toFixed(2)} c/u</p>
                            </div>
                        </div>
                    `;
                });

                elements.listaProductosSeleccionados.innerHTML = html;
                elements.totalCarrito.textContent = `Bs. ${totalGeneral.toFixed(2)}`;
                
                // Actualizar campo oculto con datos de productos
                elements.productosData.value = JSON.stringify(productosSeleccionados);
            }

            function limpiarTodaSeleccion() {
                productosSeleccionados.forEach(producto => {
                    const checkbox = document.querySelector(`#producto_${producto.index}`);
                    checkbox.checked = false;
                    checkbox.dispatchEvent(new Event('change'));
                });
            }

            // Validar formulario antes de enviar
            document.getElementById('pedidoForm').addEventListener('submit', function(e) {
                if (productosSeleccionados.length === 0) {
                    e.preventDefault();
                    alert('Por favor selecciona al menos un producto');
                    return false;
                }
                
                const direccion = document.getElementById('direccion_entrega').value.trim();
                const telefono = document.getElementById('telefono_contacto').value.trim();
                
                if (!direccion || !telefono) {
                    e.preventDefault();
                    alert('Por favor completa la dirección de entrega y teléfono de contacto');
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

            // Función para verificar stock antes de enviar el formulario
            function verificarStockAntesDePedido() {
                if (productosSeleccionados.length === 0) {
                    return Promise.resolve(true);
                }

                const productosParaVerificar = productosSeleccionados.map(producto => ({
                    id: producto.id,
                    cantidad: producto.cantidad
                }));

                return fetch('{{ route("pedidos.verificar-stock") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        productos: productosParaVerificar
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        let mensaje = 'Problemas de stock detectados:\n\n';
                        data.productos.forEach(producto => {
                            if (!producto.tiene_stock) {
                                mensaje += `• ${producto.nombre}: ${producto.mensaje}\n`;
                            }
                        });
                        alert(mensaje);
                        return false;
                    }
                    return true;
                })
                .catch(error => {
                    console.error('Error verificando stock:', error);
                    return true; // Permitir continuar si hay error en la verificación
                });
            }

            // Actualizar el evento de envío del formulario para incluir verificación de stock
            const form = document.getElementById('pedidoForm');
            const originalSubmitHandler = form.onsubmit;
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Verificaciones básicas primero
                if (productosSeleccionados.length === 0) {
                    alert('Por favor selecciona al menos un producto');
                    return false;
                }
                
                const direccion = document.getElementById('direccion_entrega').value.trim();
                const telefono = document.getElementById('telefono_contacto').value.trim();
                
                if (!direccion || !telefono) {
                    alert('Por favor completa la dirección de entrega y teléfono de contacto');
                    return false;
                }

                // Verificar stock antes de enviar
                verificarStockAntesDePedido().then(stockOk => {
                    if (stockOk) {
                        // Si el stock está bien, enviar el formulario
                        form.removeEventListener('submit', arguments.callee);
                        form.submit();
                    }
                });
            });

            // Mostrar alertas de stock bajo en tiempo real
            function mostrarAlertaStockBajo() {
                productosSeleccionados.forEach(producto => {
                    const unidades = producto.cantidad * 12;
                    const stockRestante = producto.stock - unidades;
                    
                    if (stockRestante < 0) {
                        // Stock insuficiente
                        const card = document.querySelector(`[data-id="${producto.id}"]`);
                        if (card) {
                            card.style.borderColor = '#ef4444';
                            card.style.backgroundColor = '#fef2f2';
                        }
                    } else if (stockRestante <= 5) {
                        // Stock bajo
                        const card = document.querySelector(`[data-id="${producto.id}"]`);
                        if (card) {
                            card.style.borderColor = '#f59e0b';
                            card.style.backgroundColor = '#fffbeb';
                        }
                    }
                });
            }

            // Llamar a la función de alerta cada vez que se actualice el carrito
            const originalActualizarCarritoVisual = actualizarCarritoVisual;
            actualizarCarritoVisual = function() {
                originalActualizarCarritoVisual();
                mostrarAlertaStockBajo();
            };
        });
    </script>
    @endpush
@endsection
