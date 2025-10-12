<x-app-layout>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-boom-text-dark">Crear Pedido para Cliente</h2>
                        <a href="{{ route('pedidos.index') }}" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
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
                        <div class="mb-6">
                            <label for="id_cliente" class="block text-sm font-medium text-boom-text-dark mb-2">
                                Cliente *
                            </label>
                            <select name="id_cliente" id="id_cliente" required
                                    class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent">
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
                        <div id="info-cliente" class="bg-boom-cream-100 rounded-lg p-4 mb-6" style="display: none;">
                            <h4 class="font-semibold text-boom-text-dark mb-2">Información del Cliente</h4>
                            <div id="datos-cliente"></div>
                        </div>

                        <!-- Selección de Producto por Categorías -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-6">Seleccionar Producto</h3>
                            
                            @php
                                $productosPorCategoria = collect($productos)->groupBy('categoria');
                            @endphp
                            
                            @foreach($productosPorCategoria as $categoria => $productosCategoria)
                                <div class="mb-10">
                                    <!-- Etiqueta de Categoría Simple -->
                                    <div class="mb-6">
                                        <span class="inline-flex items-center px-4 py-2 bg-boom-rose-dark text-white font-semibold rounded-lg shadow-sm">
                                            <i class="fas {{ $categoria == 'Formal' ? 'fa-user-tie' : ($categoria == 'Informal' ? 'fa-tshirt' : ($categoria == 'Deportivo' ? 'fa-running' : 'fa-tags')) }} mr-2"></i>
                                            {{ $categoria }}
                                        </span>
                                    </div>
                                    
                                    <!-- Productos de la Categoría -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                        @foreach($productosCategoria as $index => $producto)
                                            @php
                                                $globalIndex = array_search($producto, $productos);
                                            @endphp
                                            <div class="bg-white border border-boom-cream-300 rounded-xl p-5 cursor-pointer hover:shadow-lg hover:border-boom-rose-dark transition-all duration-300 producto-card" 
                                                 data-nombre="{{ $producto['nombre'] }}" 
                                                 data-precio="{{ $producto['precio'] }}" 
                                                 data-categoria="{{ $producto['categoria'] }}"
                                                 data-descripcion="{{ $producto['descripcion'] }}"
                                                 data-colores="{{ json_encode($producto['colores']) }}"
                                                 data-tallas="{{ json_encode($producto['tallas']) }}"
                                                 data-imagen="{{ asset($producto['imagen']) }}"
                                                 data-stock="{{ $producto['stock'] ?? 0 }}">
                                                
                                                <!-- Imagen de la prenda -->
                                                <div class="relative mb-4">
                                                    <img src="{{ asset($producto['imagen']) }}" 
                                                         alt="{{ $producto['nombre'] }}" 
                                                         class="w-full h-48 object-cover rounded-lg">
                                                    
                                                    <!-- Botón de previsualización -->
                                                    <button type="button" onclick="mostrarPreview({{ $globalIndex }})" 
                                                            class="absolute top-2 right-2 bg-white bg-opacity-90 hover:bg-opacity-100 text-boom-text-dark p-2 rounded-full shadow-md transition-all duration-300"
                                                            title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <!-- Radio button -->
                                                    <div class="absolute top-2 left-2">
                                                        <input type="radio" name="producto_seleccionado" value="{{ $globalIndex }}" 
                                                               id="producto_{{ $globalIndex }}" class="w-5 h-5 text-boom-rose-dark">
                                                    </div>
                                                </div>
                                                
                                                <!-- Información del producto -->
                                                <div class="space-y-2">
                                                    <label for="producto_{{ $globalIndex }}" class="block font-semibold text-boom-text-dark cursor-pointer hover:text-boom-rose-dark transition-colors">
                                                        {{ $producto['nombre'] }}
                                                    </label>
                                                    
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

                        <!-- Campos ocultos para el producto seleccionado -->
                        <input type="hidden" name="producto_nombre" id="producto_nombre" value="{{ old('producto_nombre') }}">
                        <input type="hidden" name="producto_precio" id="producto_precio" value="{{ old('producto_precio') }}">
                        <input type="hidden" name="categoria" id="categoria" value="{{ old('categoria') }}">

                        <!-- Selección de Cantidad -->
                        <div class="mb-6" id="cantidad-section" style="display: none;">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">Cantidad (Por Docenas)</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <p class="text-blue-800 text-sm mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Venta por mayoreo:</strong> Los productos se venden por docenas (12 unidades mínimo)
                                </p>
                                <ul class="text-blue-700 text-sm space-y-1">
                                    <li>• 1 docena = 12 unidades</li>
                                    <li>• 2 docenas = 24 unidades</li>
                                    <li>• 3 docenas = 36 unidades</li>
                                    <li>• Y así sucesivamente...</li>
                                </ul>
                            </div>
                            <div class="flex items-center space-x-4">
                                <label for="cantidad_docenas" class="block text-sm font-medium text-boom-text-dark">
                                    Número de docenas *
                                </label>
                                <div class="flex items-center space-x-2">
                                    <button type="button" id="btn-menos" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-bold py-2 px-3 rounded-lg transition-colors duration-300">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="cantidad_docenas" id="cantidad_docenas" min="1" value="1" 
                                           class="w-20 text-center px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent">
                                    <button type="button" id="btn-mas" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-bold py-2 px-3 rounded-lg transition-colors duration-300">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <span class="text-sm text-boom-text-medium">
                                    = <span id="total-unidades">12</span> unidades
                                </span>
                            </div>
                        </div>

                        <!-- Detalles del Pedido -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="descripcion_adicional" class="block text-sm font-medium text-boom-text-dark mb-2">
                                    Descripción Adicional
                                </label>
                                <textarea name="descripcion_adicional" id="descripcion_adicional" rows="4" 
                                          class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent"
                                          placeholder="Especifica detalles como talla, color, modificaciones especiales, etc.">{{ old('descripcion_adicional') }}</textarea>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label for="direccion_entrega" class="block text-sm font-medium text-boom-text-dark mb-2">
                                        Dirección de Entrega
                                    </label>
                                    <textarea name="direccion_entrega" id="direccion_entrega" rows="2" 
                                              class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent"
                                              placeholder="Dirección específica para la entrega (opcional)">{{ old('direccion_entrega') }}</textarea>
                                </div>
                                <div>
                                    <label for="telefono_contacto" class="block text-sm font-medium text-boom-text-dark mb-2">
                                        Teléfono de Contacto
                                    </label>
                                    <input type="tel" name="telefono_contacto" id="telefono_contacto"
                                           class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent"
                                           placeholder="Teléfono alternativo (opcional)" value="{{ old('telefono_contacto') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Asignación de Operario (solo para administradores) -->
                        @if(Auth::user()->id_rol == 1 && count($operarios) > 0)
                            <div class="mb-6">
                                <label for="id_operario" class="block text-sm font-medium text-boom-text-dark mb-2">
                                    Asignar Operario (Opcional)
                                </label>
                                <select name="id_operario" id="id_operario"
                                        class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark focus:border-transparent">
                                    <option value="">Sin asignar (se asignará después)</option>
                                    @foreach($operarios as $operario)
                                        <option value="{{ $operario->id_usuario }}" {{ old('id_operario') == $operario->id_usuario ? 'selected' : '' }}>
                                            {{ $operario->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-boom-text-medium mt-1">
                                    Si asignas un operario, el pedido pasará directamente al estado "Asignado"
                                </p>
                            </div>
                        @endif

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
                            <a href="{{ route('pedidos.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                Cancelar
                            </a>
                            <button type="submit" id="btn-crear-pedido" disabled
                                    class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <p><strong>Nombre:</strong> ${option.text.split(' - CI:')[0]}</p>
                            <p><strong>CI/NIT:</strong> ${ci}</p>
                            <p><strong>Teléfono:</strong> ${telefono || 'No registrado'}</p>
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
                    actualizarResumen();
                    productoSeleccionado = true;
                    actualizarBoton();
                });
            });

            // Manejar botones de cantidad
            btnMas.addEventListener('click', function() {
                const cantidad = parseInt(cantidadDocenas.value) + 1;
                cantidadDocenas.value = cantidad;
                actualizarCantidad();
                actualizarResumen();
            });

            btnMenos.addEventListener('click', function() {
                const cantidad = Math.max(1, parseInt(cantidadDocenas.value) - 1);
                cantidadDocenas.value = cantidad;
                actualizarCantidad();
                actualizarResumen();
            });

            cantidadDocenas.addEventListener('input', function() {
                const cantidad = Math.max(1, parseInt(this.value) || 1);
                this.value = cantidad;
                actualizarCantidad();
                actualizarResumen();
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><strong>Cliente:</strong> ${clienteTexto}</p>
                                <p><strong>Producto:</strong> ${nombre}</p>
                                <p><strong>Categoría:</strong> ${cat}</p>
                                <p><strong>Cantidad:</strong> ${docenas} docena${docenas > 1 ? 's' : ''} (${unidades} unidades)</p>
                                ${descripcionHtml}
                            </div>
                            <div>
                                <p><strong>Precio por docena:</strong> Bs. ${precio.toFixed(2)}</p>
                                <div class="mt-2">
                                    <p class="text-sm font-medium">Colores disponibles:</p>
                                    <div class="mt-1">${coloresHtml}</div>
                                </div>
                                <div class="mt-2">
                                    <p class="text-sm font-medium">Tallas disponibles:</p>
                                    <div class="mt-1">${tallasHtml}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    totalPrecio.textContent = precioTotal.toFixed(2);
                    resumenPedido.style.display = 'block';
                }
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
</x-app-layout>