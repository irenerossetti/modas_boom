@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <!-- Icono de éxito -->
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100">
                    <i class="fas fa-check text-3xl text-green-600"></i>
                </div>
            </div>
            
            <!-- Título -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                ¡Pago Procesado Exitosamente!
            </h1>
            
            <!-- Mensaje -->
            <p class="text-lg text-gray-600 mb-6">
                Tu pago ha sido registrado correctamente en nuestro sistema.
            </p>
            
            <!-- Detalles del pago -->
            <div id="detallesPago" class="bg-gray-50 rounded-lg p-6 mb-6 text-left" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Detalles del Pago:</h3>
                <div class="grid grid-cols-2 gap-4" id="detallesGrid">
                    <!-- Los detalles se llenarán con JavaScript -->
                </div>
            </div>
            
            @if(session('pago_detalles'))
                <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Detalles del Pago:</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @if(session('pago_detalles.pedido_id'))
                            <div>
                                <p class="text-sm text-gray-500">Pedido</p>
                                <p class="font-bold text-gray-800">#{{ session('pago_detalles.pedido_id') }}</p>
                            </div>
                        @endif
                        @if(session('pago_detalles.monto'))
                            <div>
                                <p class="text-sm text-gray-500">Monto</p>
                                <p class="font-bold text-gray-800">Bs. {{ number_format(session('pago_detalles.monto'), 2) }}</p>
                            </div>
                        @endif
                        @if(session('pago_detalles.metodo'))
                            <div>
                                <p class="text-sm text-gray-500">Método de Pago</p>
                                <p class="font-bold text-gray-800">{{ ucfirst(session('pago_detalles.metodo')) }}</p>
                            </div>
                        @endif
                        @if(session('pago_detalles.fecha'))
                            <div>
                                <p class="text-sm text-gray-500">Fecha</p>
                                <p class="font-bold text-gray-800">{{ session('pago_detalles.fecha') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Botones de acción -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('dashboard') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i>
                    Ir al Dashboard
                </a>
                
                @if(session('pago_detalles.pedido_id'))
                    <a href="{{ route('pedidos.show', session('pago_detalles.pedido_id')) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                        <i class="fas fa-eye mr-2"></i>
                        Ver Pedido
                    </a>
                @endif
            </div>
            
            <!-- Mensaje adicional -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Recibirás una confirmación por email con los detalles de tu pago.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si hay detalles del pago en sessionStorage
    const pagoDetalles = sessionStorage.getItem('pago_detalles');
    if (pagoDetalles) {
        try {
            const detalles = JSON.parse(pagoDetalles);
            const detallesContainer = document.getElementById('detallesPago');
            const detallesGrid = document.getElementById('detallesGrid');
            
            let html = '';
            
            if (detalles.pedido_id) {
                html += `
                    <div>
                        <p class="text-sm text-gray-500">Pedido</p>
                        <p class="font-bold text-gray-800">#${detalles.pedido_id}</p>
                    </div>
                `;
            }
            
            if (detalles.monto) {
                html += `
                    <div>
                        <p class="text-sm text-gray-500">Monto</p>
                        <p class="font-bold text-gray-800">Bs. ${parseFloat(detalles.monto).toFixed(2)}</p>
                    </div>
                `;
            }
            
            if (detalles.metodo) {
                html += `
                    <div>
                        <p class="text-sm text-gray-500">Método de Pago</p>
                        <p class="font-bold text-gray-800">${detalles.metodo}</p>
                    </div>
                `;
            }
            
            if (detalles.fecha) {
                html += `
                    <div>
                        <p class="text-sm text-gray-500">Fecha</p>
                        <p class="font-bold text-gray-800">${detalles.fecha}</p>
                    </div>
                `;
            }
            
            if (html) {
                detallesGrid.innerHTML = html;
                detallesContainer.style.display = 'block';
            }
            
            // Limpiar sessionStorage
            sessionStorage.removeItem('pago_detalles');
        } catch (e) {
            console.error('Error al procesar detalles del pago:', e);
        }
    }
});
</script>