@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-2xl mx-auto">
            <!-- Mensaje de éxito -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-green-800">
                            ¡Pedido Creado Exitosamente!
                        </h1>
                        <p class="text-green-700 mt-1">
                            Su pedido ha sido registrado y está siendo procesado.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detalles del pedido -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-boom-primary text-white p-6">
                    <h2 class="text-xl font-semibold flex items-center">
                        <i class="fas fa-receipt mr-2"></i>
                        Detalles del Pedido #{{ $pedido->id_pedido }}
                    </h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información del pedido -->
                        <div>
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-shopping-bag mr-2"></i>
                                Información del Pedido
                            </h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Número de Pedido:</span>
                                    <span class="font-bold text-boom-primary">#{{ $pedido->id_pedido }}</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Estado:</span>
                                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $pedido->estado_color }}">
                                        <i class="{{ $pedido->estado_icono }} mr-1"></i>
                                        {{ $pedido->estado }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total:</span>
                                    <span class="font-bold text-boom-text-dark text-lg">
                                        {{ $pedido->total_formateado }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fecha de Creación:</span>
                                    <span class="font-medium">
                                        {{ $pedido->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Información del cliente -->
                        <div>
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-user mr-2"></i>
                                Información del Cliente
                            </h3>
                            
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center mb-3">
                                    <div class="w-12 h-12 bg-boom-primary rounded-full flex items-center justify-center text-white font-bold mr-3">
                                        {{ strtoupper(substr($pedido->cliente->nombre, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-boom-text-dark">
                                            {{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            CI/NIT: {{ $pedido->cliente->ci_nit }}
                                        </div>
                                    </div>
                                </div>
                                
                                @if($pedido->cliente->telefono)
                                <div class="text-sm text-gray-600 mb-1">
                                    <i class="fas fa-phone mr-2"></i>
                                    {{ $pedido->cliente->telefono }}
                                </div>
                                @endif
                                
                                @if($pedido->cliente->email)
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-envelope mr-2"></i>
                                    {{ $pedido->cliente->email }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Próximos pasos -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    Próximos Pasos
                </h3>
                <ul class="text-blue-700 space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-600 mr-2 mt-1"></i>
                        <span>Su pedido ha sido registrado con el número <strong>#{{ $pedido->id_pedido }}</strong></span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-clock text-blue-600 mr-2 mt-1"></i>
                        <span>Nuestro equipo revisará su pedido y se pondrá en contacto con usted</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-phone text-blue-600 mr-2 mt-1"></i>
                        <span>Le confirmaremos los detalles y coordinaremos la toma de medidas</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-calendar text-blue-600 mr-2 mt-1"></i>
                        <span>Le informaremos sobre los tiempos de confección y entrega</span>
                    </li>
                </ul>
            </div>

            <!-- Acciones -->
            <div class="flex flex-col sm:flex-row gap-4 mt-8">
                <a href="{{ route('catalogo.index') }}" 
                   class="flex-1 bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-3 px-6 rounded-lg text-center transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Catálogo
                </a>
                
                <button onclick="window.print()" 
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                    <i class="fas fa-print mr-2"></i>
                    Imprimir Confirmación
                </button>
            </div>

            <!-- Información de contacto -->
            <div class="bg-boom-cream-100 rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-boom-text-dark mb-3">
                    <i class="fas fa-headset mr-2"></i>
                    ¿Necesita Ayuda?
                </h3>
                <p class="text-boom-text-dark mb-3">
                    Si tiene alguna pregunta sobre su pedido, no dude en contactarnos:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-center">
                        <i class="fas fa-phone text-boom-primary mr-2"></i>
                        <span>Teléfono: (591) 123-4567</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope text-boom-primary mr-2"></i>
                        <span>Email: info@modasboom.com</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock text-boom-primary mr-2"></i>
                        <span>Horario: Lun-Vie 8:00-18:00</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-boom-primary mr-2"></i>
                        <span>Dirección: Av. Principal 123</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background: white !important;
            }
            
            .bg-boom-primary {
                background: #1f2937 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
    @endpush
@endsection
