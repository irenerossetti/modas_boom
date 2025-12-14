@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-credit-card mr-3 text-blue-600"></i>
                Pasarela de Pagos
            </h1>
            @if(isset($pedidoRecienCreado) && $pedidoRecienCreado)
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 inline-block">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong>¡Pedido #{{ $pedidoRecienCreado }} creado exitosamente!</strong> 
                    <br>Selecciona el método de pago para completar la transacción.
                </div>
            @endif
            <p class="text-gray-600">Procesar pagos de pedidos - Panel de {{ Auth::user()->rol->nombre }}</p>
        </div>

        @if(isset($pedidoRecienCreado) && $pedidoRecienCreado)
            <!-- PEDIDO RECIÉN CREADO - Mostrar formulario automáticamente -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-payment mr-2 text-green-600"></i>
                        Método de Pago
                    </h4>
                    
                    <form id="formPagoReciente" action="{{ route('pagos.procesar-pasarela') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="pedido_id" value="{{ $pedidoRecienCreado }}">
                        
                        <!-- Resumen del Pedido Recién Creado -->
                        @php
                            $pedidoReciente = \App\Models\Pedido::with('cliente')->find($pedidoRecienCreado);
                        @endphp
                        
                        @if($pedidoReciente)
                        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Pedido</p>
                                    <p class="font-bold text-gray-800">#{{ $pedidoReciente->id_pedido }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Cliente</p>
                                    <p class="font-bold text-gray-800">{{ $pedidoReciente->cliente->nombre }} {{ $pedidoReciente->cliente->apellido }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-500">Total a Pagar</p>
                                    <p class="text-2xl font-bold text-blue-600">Bs. {{ number_format($pedidoReciente->total, 2) }}</p>
                                </div>
                            </div>
                        </div>



                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                            @forelse($metodosPago ?? [] as $metodo)
                                <label class="payment-method cursor-pointer" data-metodo="{{ $metodo->nombre }}">
                                    <input type="radio" name="metodo" value="{{ strtolower(str_replace(' ', '_', $metodo->nombre)) }}" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 text-center transition-colors hover:border-opacity-70"
                                         style="--hover-color: {{ $metodo->color }}">
                                        <i class="{{ $metodo->icono }} text-2xl mb-2" style="color: {{ $metodo->color }}"></i>
                                        <p class="text-sm font-medium">{{ $metodo->nombre }}</p>
                                    </div>
                                </label>
                            @empty
                                <!-- Métodos por defecto si no hay configurados -->
                                <label class="payment-method cursor-pointer">
                                    <input type="radio" name="metodo" value="efectivo" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-green-500 transition-colors">
                                        <i class="fas fa-money-bill-wave text-2xl text-green-600 mb-2"></i>
                                        <p class="text-sm font-medium">Efectivo</p>
                                    </div>
                                </label>
                                
                                <label class="payment-method cursor-pointer">
                                    <input type="radio" name="metodo" value="tarjeta" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-blue-500 transition-colors">
                                        <i class="fas fa-credit-card text-2xl text-blue-600 mb-2"></i>
                                        <p class="text-sm font-medium">Tarjeta</p>
                                    </div>
                                </label>

                                <label class="payment-method cursor-pointer">
                                    <input type="radio" name="metodo" value="qr" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-purple-500 transition-colors">
                                        <i class="fas fa-qrcode text-2xl text-purple-600 mb-2"></i>
                                        <p class="text-sm font-medium">QR</p>
                                    </div>
                                </label>
                            @endforelse
                        </div>

                        <!-- Campos dinámicos según método de pago -->
                        <div id="camposAdicionalesReciente" class="hidden space-y-4 border-t pt-4">
                            <!-- Monto a Pagar -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Monto a Pagar (Bs.)</label>
                                <input type="number" step="0.01" name="monto" id="montoPagoReciente" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="{{ $pedidoReciente->total }}">
                            </div>

                            <!-- Campos Transferencia -->
                            <div id="camposTransferenciaReciente" class="hidden">
                                <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                    <p class="text-sm text-blue-800 font-medium">Datos Bancarios:</p>
                                    <p class="text-sm text-blue-600">Banco: BNB<br>Cuenta: 123-456-789<br>Titular: Modas Boom</p>
                                </div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Comprobante de Transferencia</label>
                                <input type="text" name="comprobante" placeholder="Número de comprobante"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Campos Stripe -->
                            <div id="camposStripeReciente" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Datos de Tarjeta</label>
                                <div id="stripe-card-element-reciente" class="p-4 border border-gray-300 rounded-lg bg-white">
                                    <!-- Stripe Element -->
                                </div>
                                <div id="stripe-card-errors-reciente" class="mt-2 text-sm text-red-600 hidden" role="alert"></div>
                            </div>

                            <!-- Campos QR -->
                            <div id="camposQRReciente" class="hidden text-center">
                                <!-- Imagen QR dinámica -->
                                <div class="max-w-sm mx-auto">
                                    <div id="qr-image-container-reciente">
                                        <!-- La imagen se cargará dinámicamente -->
                                    </div>
                                </div>
                                
                                <!-- Campo para referencia del pago -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Referencia del Pago (Opcional)</label>
                                    <input type="text" name="referencia_qr" placeholder="Ingresa la referencia de tu pago"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                    <p class="text-xs text-gray-500 mt-1">Ingresa el número de referencia que aparece en tu comprobante de pago</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" onclick="window.location.href='{{ route('dashboard') }}'" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Volver al Dashboard
                            </button>
                            <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                <i class="fas fa-check-circle mr-2"></i>
                                Confirmar Pago
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Panel de Búsqueda de Pedidos -->
                <div id="estadoInicial" class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-search mr-2 text-blue-600"></i>
                        Buscar Pedido
                    </h3>
                    
                    <form id="buscarPedidoForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número de Pedido</label>
                            <input type="number" id="numeroPedido" placeholder="Ej: 123" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">O buscar por Cliente</label>
                            <input type="text" id="nombreCliente" placeholder="Nombre del cliente" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>
                            Buscar Pedido
                        </button>
                    </form>

                    <!-- Pedidos Pendientes de Pago -->
                    <div class="mt-6">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Pedidos Pendientes</h4>
                        <div id="pedidosPendientes" class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach($pedidosPendientes ?? [] as $pedido)
                            <div class="p-3 border rounded-lg cursor-pointer pedido-item 
                                        @if(isset($pedidoRecienCreado) && $pedidoRecienCreado == $pedido->id_pedido)
                                            border-green-400 bg-green-50 hover:bg-green-100 ring-2 ring-green-200
                                        @else
                                            border-gray-200 hover:bg-gray-50
                                        @endif" 
                                 data-pedido-id="{{ $pedido->id_pedido }}"
                                 data-cliente="{{ $pedido->cliente->nombre ?? 'N/A' }}"
                                 data-total="{{ $pedido->total }}">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="flex items-center">
                                            <p class="font-semibold text-sm">#{{ $pedido->id_pedido }}</p>
                                            @if(isset($pedidoRecienCreado) && $pedidoRecienCreado == $pedido->id_pedido)
                                                <span class="ml-2 text-xs px-2 py-1 bg-green-500 text-white rounded-full">NUEVO</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-600">{{ $pedido->cliente->nombre ?? 'N/A' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-sm">Bs. {{ number_format($pedido->total, 2) }}</p>
                                        <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Pendiente</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Detalles del Pago -->
            <div class="@if(isset($pedidoSeleccionado)) lg:col-span-3 max-w-2xl mx-auto w-full @else lg:col-span-2 @endif">
                <div id="pedidoSeleccionado" class="bg-white rounded-xl shadow-lg p-6 @if(!isset($pedidoRecienCreado) || !$pedidoRecienCreado) hidden @endif">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-payment mr-2 text-green-600"></i>
                        Método de Pago
                    </h4>
                    
                    <form id="formPago" action="{{ route('pagos.procesar-pasarela') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="pedido_id" id="pedidoIdHidden" value="{{ isset($pedidoSeleccionado) ? $pedidoSeleccionado->id_pedido : '' }}">
                        
                        <!-- Resumen del Pedido Seleccionado -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Pedido</p>
                                    <p class="font-bold text-gray-800" id="pedidoNumero">
                                        {{ isset($pedidoSeleccionado) ? '#' . $pedidoSeleccionado->id_pedido : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Cliente</p>
                                    <p class="font-bold text-gray-800" id="pedidoCliente">
                                        {{ isset($pedidoSeleccionado) ? ($pedidoSeleccionado->cliente->nombre . ' ' . $pedidoSeleccionado->cliente->apellido) : '-' }}
                                    </p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-500">Total a Pagar</p>
                                    <p class="text-2xl font-bold text-blue-600" id="pedidoTotal">
                                        {{ isset($pedidoSeleccionado) ? 'Bs. ' . number_format($pedidoSeleccionado->total, 2) : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                            @forelse($metodosPago ?? [] as $metodo)
                                <label class="payment-method cursor-pointer" data-metodo="{{ $metodo->nombre }}">
                                    <input type="radio" name="metodo" value="{{ strtolower(str_replace(' ', '_', $metodo->nombre)) }}" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 text-center transition-colors hover:border-opacity-70"
                                         style="--hover-color: {{ $metodo->color }}">
                                        <i class="{{ $metodo->icono }} text-2xl mb-2" style="color: {{ $metodo->color }}"></i>
                                        <p class="text-sm font-medium">{{ $metodo->nombre }}</p>
                                    </div>
                                </label>
                            @empty
                                <!-- Métodos por defecto si no hay configurados -->
                                <label class="payment-method cursor-pointer">
                                    <input type="radio" name="metodo" value="efectivo" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-green-500 transition-colors">
                                        <i class="fas fa-money-bill-wave text-2xl text-green-600 mb-2"></i>
                                        <p class="text-sm font-medium">Efectivo</p>
                                    </div>
                                </label>
                                
                                <label class="payment-method cursor-pointer">
                                    <input type="radio" name="metodo" value="tarjeta" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-blue-500 transition-colors">
                                        <i class="fas fa-credit-card text-2xl text-blue-600 mb-2"></i>
                                        <p class="text-sm font-medium">Tarjeta</p>
                                    </div>
                                </label>

                                <label class="payment-method cursor-pointer">
                                    <input type="radio" name="metodo" value="qr" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-purple-500 transition-colors">
                                        <i class="fas fa-qrcode text-2xl text-purple-600 mb-2"></i>
                                        <p class="text-sm font-medium">QR</p>
                                    </div>
                                </label>
                            @endforelse
                        </div>

                        <!-- Campos dinámicos según método de pago -->
                        <div id="camposAdicionales" class="hidden space-y-4 border-t pt-4">
                            <!-- Monto a Pagar -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Monto a Pagar (Bs.)</label>
                                <input type="number" step="0.01" name="monto" id="montoPago" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="{{ isset($pedidoSeleccionado) ? $pedidoSeleccionado->total : '' }}">
                            </div>

                            <!-- Campos Transferencia -->
                            <div id="camposTransferencia" class="hidden">
                                <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                    <p class="text-sm text-blue-800 font-medium">Datos Bancarios:</p>
                                    <p class="text-sm text-blue-600">Banco: BNB<br>Cuenta: 123-456-789<br>Titular: Modas Boom</p>
                                </div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Comprobante de Transferencia</label>
                                <input type="text" name="comprobante" placeholder="Número de comprobante"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Campos Stripe -->
                            <div id="camposStripe" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Datos de Tarjeta</label>
                                <div id="stripe-card-element" class="p-4 border border-gray-300 rounded-lg bg-white">
                                    <!-- Stripe Element -->
                                </div>
                                <div id="stripe-card-errors" class="mt-2 text-sm text-red-600 hidden" role="alert"></div>
                            </div>

                            <!-- Campos QR -->
                            <div id="camposQR" class="hidden text-center">
                                <!-- Imagen QR dinámica -->
                                <div class="max-w-sm mx-auto">
                                    <div id="qr-image-container">
                                        <!-- La imagen se cargará dinámicamente -->
                                    </div>
                                </div>
                                
                                <!-- Campo para referencia del pago -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Referencia del Pago (Opcional)</label>
                                    <input type="text" name="referencia_qr" placeholder="Ingresa la referencia de tu pago"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                    <p class="text-xs text-gray-500 mt-1">Ingresa el número de referencia que aparece en tu comprobante de pago</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            @if(isset($pedidoSeleccionado) && $pedidoSeleccionado)
                                <button type="button" id="btnCambiarPedido" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Volver al Dashboard
                                </button>
                            @else
                                <button type="button" id="btnCancelar" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancelar
                                </button>
                            @endif
                            <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                <i class="fas fa-check-circle mr-2"></i>
                                Confirmar Pago
                            </button>

                        </div>
                    </form>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>

<style>
.payment-method input:checked + div {
    border-color: #10b981;
    background-color: #f0fdf4;
}

.payment-method input:checked + div i {
    color: #10b981;
}

.pedido-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const buscarForm = document.getElementById('buscarPedidoForm');
    const pedidosPendientes = document.querySelectorAll('.pedido-item');
    const estadoInicial = document.getElementById('estadoInicial');
    const pedidoSeleccionado = document.getElementById('pedidoSeleccionado');
    const formPago = document.getElementById('formPago');
    const metodoPago = document.querySelectorAll('input[name="metodo"]');
    const camposAdicionales = document.getElementById('camposAdicionales');

    // Pedido ya pre-seleccionado desde el servidor
    @if(isset($pedidoSeleccionado) && $pedidoSeleccionado)
        // El pedido ya está seleccionado, solo mostrar mensaje de confirmación
        setTimeout(() => {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4';
            alertDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Pedido #{{ $pedidoSeleccionado->id_pedido }} listo para pago. Selecciona tu método de pago preferido.';
            document.querySelector('#pedidoSeleccionado').insertBefore(alertDiv, document.querySelector('#pedidoSeleccionado').firstChild);
            
            // Remover el mensaje después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }, 500);
    @endif
    
    // Seleccionar pedido de la lista (solo si existe)
    if (pedidosPendientes.length > 0) {
        pedidosPendientes.forEach(item => {
            item.addEventListener('click', function() {
                const pedidoId = this.dataset.pedidoId;
                const cliente = this.dataset.cliente;
                const total = this.dataset.total;
                
                seleccionarPedido(pedidoId, cliente, total);
            });
        });
    }
    
    // Buscar pedido por formulario (solo si existe)
    if (buscarForm) {
        buscarForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const numeroPedido = document.getElementById('numeroPedido').value;
            const nombreCliente = document.getElementById('nombreCliente').value;
            
            if (numeroPedido) {
                buscarPedidoPorNumero(numeroPedido);
            } else if (nombreCliente) {
                buscarPedidoPorCliente(nombreCliente);
            }
        });
    }
    
    // Manejar cambios en método de pago (formulario original)
    metodoPago.forEach(radio => {
        radio.addEventListener('change', function() {
            const label = this.closest('.payment-method');
            const metodoNombre = label ? label.dataset.metodo : this.value;
            mostrarCamposMetodo(this.value, metodoNombre);
        });
    });
    
    // Manejar cambios en método de pago (formulario pedido reciente)
    const metodoPagoReciente = document.querySelectorAll('#formPagoReciente input[name="metodo"]');
    metodoPagoReciente.forEach(radio => {
        radio.addEventListener('change', function() {
            const label = this.closest('.payment-method');
            const metodoNombre = label ? label.dataset.metodo : this.value;
            mostrarCamposMetodoReciente(this.value, metodoNombre);
        });
    });
    

    
    // Cancelar selección (solo si existe)
    const btnCancelar = document.getElementById('btnCancelar');
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            // Redirigir al dashboard en lugar de limpiar selección
            window.location.href = '{{ route("dashboard") }}';
        });
    }

    // Cambiar pedido (solo si existe)
    const btnCambiarPedido = document.getElementById('btnCambiarPedido');
    if (btnCambiarPedido) {
        btnCambiarPedido.addEventListener('click', function() {
            // Redirigir al dashboard en lugar de mostrar búsqueda
            window.location.href = '{{ route("dashboard") }}';
        });
    }
    
    function seleccionarPedido(id, cliente, total) {
        document.getElementById('pedidoNumero').textContent = '#' + id;
        document.getElementById('pedidoCliente').textContent = cliente;
        document.getElementById('pedidoTotal').textContent = 'Bs. ' + parseFloat(total).toFixed(2);
        document.getElementById('pedidoIdHidden').value = id;
        document.getElementById('montoPago').value = parseFloat(total).toFixed(2);
        

        
        estadoInicial.classList.add('hidden');
        pedidoSeleccionado.classList.remove('hidden');
    }
    
    function limpiarSeleccion() {
        estadoInicial.classList.remove('hidden');
        pedidoSeleccionado.classList.add('hidden');
        formPago.reset();
        camposAdicionales.classList.add('hidden');
    }
    
    function mostrarCamposMetodo(metodo, metodoNombre = null) {
        // Ocultar todos los campos adicionales
        document.getElementById('camposTransferencia').classList.add('hidden');
        document.getElementById('camposStripe').classList.add('hidden');
        document.getElementById('camposQR').classList.add('hidden');
        
        // Buscar el método en los datos disponibles para obtener el tipo
        const metodoData = @json($metodosPago ?? []).find(m => 
            m.nombre === metodoNombre || 
            metodo.toLowerCase() === m.nombre.toLowerCase().replace(/\s+/g, '_')
        );
        
        const tipoMetodo = metodoData ? metodoData.tipo : null;
        const metodoLower = metodo.toLowerCase();
        
        if (tipoMetodo === 'manual' || metodoLower.includes('transferencia') || metodoLower.includes('banco')) {
            camposAdicionales.classList.remove('hidden');
            document.getElementById('camposTransferencia').classList.remove('hidden');
        } else if (tipoMetodo === 'automatico' || metodoLower.includes('stripe') || metodoLower.includes('tarjeta')) {
            camposAdicionales.classList.remove('hidden');
            document.getElementById('camposStripe').classList.remove('hidden');
            initializeStripe();
        } else if (tipoMetodo === 'qr' || metodoLower.includes('qr')) {
            camposAdicionales.classList.remove('hidden');
            document.getElementById('camposQR').classList.remove('hidden');
            // Mostrar QR personalizado si existe
            mostrarQRPersonalizado(metodoNombre || metodo);
        } else {
            camposAdicionales.classList.add('hidden');
        }
    }

    // Función para mostrar QR personalizado
    function mostrarQRPersonalizado(metodo) {
        const container = document.getElementById('qr-image-container');
        if (container) {
            console.log('Buscando QR para método:', metodo);
            console.log('Métodos disponibles:', @json($metodosPago ?? []));
            
            // Buscar cualquier método de tipo QR que tenga imagen
            const metodosQR = @json($metodosPago ?? []).filter(m => m.tipo === 'qr' && m.qr_image_url);
            console.log('Métodos QR encontrados:', metodosQR);
            
            if (metodosQR.length > 0) {
                // Usar el primer método QR disponible con imagen
                const metodoQR = metodosQR[0];
                container.innerHTML = `
                    <img src="${metodoQR.qr_image_url}" 
                         alt="QR ${metodoQR.nombre}" 
                         class="w-full h-auto rounded-lg shadow-lg">
                    <p class="text-sm text-gray-600 mt-2">${metodoQR.nombre}</p>
                `;
            } else {
                // Imagen por defecto si no hay QR personalizado
                container.innerHTML = `
                    <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
                        <i class="fas fa-qrcode text-4xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">QR de Pago</p>
                        <p class="text-xs text-gray-500 mt-1">No hay imagen QR configurada</p>
                    </div>
                `;
            }
        }
    }

    // Función para mostrar QR personalizado (formulario reciente)
    function mostrarQRPersonalizadoReciente(metodo) {
        const container = document.getElementById('qr-image-container-reciente');
        if (container) {
            console.log('Buscando QR para método (reciente):', metodo);
            
            // Buscar cualquier método de tipo QR que tenga imagen
            const metodosQR = @json($metodosPago ?? []).filter(m => m.tipo === 'qr' && m.qr_image_url);
            console.log('Métodos QR encontrados (reciente):', metodosQR);
            
            if (metodosQR.length > 0) {
                // Usar el primer método QR disponible con imagen
                const metodoQR = metodosQR[0];
                container.innerHTML = `
                    <img src="${metodoQR.qr_image_url}" 
                         alt="QR ${metodoQR.nombre}" 
                         class="w-full h-auto rounded-lg shadow-lg">
                    <p class="text-sm text-gray-600 mt-2">${metodoQR.nombre}</p>
                `;
            } else {
                // Imagen por defecto si no hay QR personalizado
                container.innerHTML = `
                    <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
                        <i class="fas fa-qrcode text-4xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">QR de Pago</p>
                        <p class="text-xs text-gray-500 mt-1">No hay imagen QR configurada</p>
                    </div>
                `;
            }
        }
    }

    // Función para mostrar campos del método de pago (formulario pedido reciente)
    function mostrarCamposMetodoReciente(metodo, metodoNombre = null) {
        
        // Ocultar todos los campos adicionales del formulario reciente
        const camposAdicionalesReciente = document.getElementById('camposAdicionalesReciente');
        document.getElementById('camposTransferenciaReciente').classList.add('hidden');
        document.getElementById('camposStripeReciente').classList.add('hidden');
        document.getElementById('camposQRReciente').classList.add('hidden');
        
        // Buscar el método en los datos disponibles para obtener el tipo
        const metodoData = @json($metodosPago ?? []).find(m => 
            m.nombre === metodoNombre || 
            metodo.toLowerCase() === m.nombre.toLowerCase().replace(/\s+/g, '_')
        );
        
        const tipoMetodo = metodoData ? metodoData.tipo : null;
        const metodoLower = metodo.toLowerCase();
        
        if (tipoMetodo === 'manual' || metodoLower.includes('transferencia') || metodoLower.includes('banco')) {
            camposAdicionalesReciente.classList.remove('hidden');
            document.getElementById('camposTransferenciaReciente').classList.remove('hidden');
        } else if (tipoMetodo === 'automatico' || metodoLower.includes('stripe') || metodoLower.includes('tarjeta')) {
            camposAdicionalesReciente.classList.remove('hidden');
            document.getElementById('camposStripeReciente').classList.remove('hidden');
            initializeStripeReciente();
        } else if (tipoMetodo === 'qr' || metodoLower.includes('qr') || metodoLower.includes('personalizado')) {
            camposAdicionalesReciente.classList.remove('hidden');
            document.getElementById('camposQRReciente').classList.remove('hidden');
            // Mostrar QR personalizado si existe
            mostrarQRPersonalizadoReciente(metodoNombre || metodo);
        } else {
            camposAdicionalesReciente.classList.add('hidden');
        }
    }
    
    function buscarPedidoPorNumero(numero) {
        // Aquí implementarías la búsqueda AJAX
        fetch(`/api/pedidos/buscar/${numero}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    seleccionarPedido(data.pedido.id, data.pedido.cliente, data.pedido.total);
                } else {
                    alert('Pedido no encontrado');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al buscar el pedido');
            });
    }

    // Variables para Stripe
    let stripe = null;
    let elements = null;
    let cardElement = null;

    // Inicializar Stripe
    function initializeStripe() {
        if (!stripe) {
            stripe = Stripe('{{ config("services.stripe.publishable") }}');
            elements = stripe.elements();
            
            cardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#424770',
                        '::placeholder': {
                            color: '#aab7c4',
                        },
                    },
                },
            });
            
            cardElement.mount('#stripe-card-element');
            
            cardElement.on('change', function(event) {
                const displayError = document.getElementById('stripe-card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                    displayError.classList.remove('hidden');
                } else {
                    displayError.textContent = '';
                    displayError.classList.add('hidden');
                }
            });
        }
    }

    // Inicializar Stripe para formulario reciente
    function initializeStripeReciente() {
        if (!stripe) {
            stripe = Stripe('{{ config("services.stripe.publishable") }}');
            elements = stripe.elements();
        }
        
        // Crear elemento de tarjeta para el formulario reciente
        const cardElementReciente = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
            },
        });
        
        cardElementReciente.mount('#stripe-card-element-reciente');
        
        cardElementReciente.on('change', function(event) {
            const displayError = document.getElementById('stripe-card-errors-reciente');
            if (event.error) {
                displayError.textContent = event.error.message;
                displayError.classList.remove('hidden');
            } else {
                displayError.textContent = '';
                displayError.classList.add('hidden');
            }
        });
    }



    // Procesar pago con Stripe
    formPago.addEventListener('submit', function(e) {
        const metodo = document.querySelector('input[name="metodo"]:checked')?.value;
        
        if (metodo === 'stripe') {
            e.preventDefault();
            procesarPagoStripe();
        }
    });

    function procesarPagoStripe() {
        const pedidoId = document.getElementById('pedidoIdHidden').value;
        const monto = document.getElementById('montoPago').value;

        if (!pedidoId) {
            alert('Por favor selecciona un pedido primero');
            return;
        }

        if (!monto || parseFloat(monto) <= 0) {
            alert('El monto debe ser mayor a 0');
            return;
        }

        // Crear Payment Intent
        const dataToSend = {
            pedido_id: pedidoId,
            amount: parseFloat(monto)
        };
        

        
        fetch('{{ route("api.stripe.create-payment-intent") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(dataToSend)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return stripe.confirmCardPayment(data.client_secret, {
                    payment_method: {
                        card: cardElement,
                    }
                });
            } else {
                throw new Error(data.error);
            }
        })
        .then(result => {
            if (result.error) {
                document.getElementById('stripe-card-errors').textContent = result.error.message;
                document.getElementById('stripe-card-errors').classList.remove('hidden');
            } else {
                // Pago exitoso
                confirmarPagoStripe(result.paymentIntent.id, pedidoId);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar el pago: ' + error.message);
        });
    }

    function confirmarPagoStripe(paymentIntentId, pedidoId) {
        fetch('{{ route("api.stripe.confirm-payment") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                payment_intent_id: paymentIntentId,
                pedido_id: pedidoId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Guardar detalles del pago en sessionStorage para la página de éxito
                if (data.pago_detalles) {
                    sessionStorage.setItem('pago_detalles', JSON.stringify(data.pago_detalles));
                }
                window.location.href = data.redirect_url;
            } else {
                // Guardar detalles del error para la página de error
                sessionStorage.setItem('error_mensaje', data.error);
                window.location.href = data.redirect_url || '{{ route("pago.error") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            sessionStorage.setItem('error_mensaje', 'Error de conexión al procesar el pago');
            window.location.href = '{{ route("pago.error") }}';
        });
    }


});
</script>

<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>

<script>
// Prevención de doble click en formularios de pago
document.addEventListener('DOMContentLoaded', function() {
    const forms = ['formPagoReciente', 'formPago'];
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    // Prevenir múltiples envíos
                    if (submitBtn.disabled) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Deshabilitar botón y cambiar texto
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
                    
                    // Reactivar después de 10 segundos por si hay error
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 10000);
                }
            });
        }
    });
});
</script>

@endsection

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush