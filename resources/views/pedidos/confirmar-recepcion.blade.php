@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                Confirmar Recepción de Pedido
                            </h1>
                            <p class="text-gray-600 mt-1">Pedido #{{ $pedido->id_pedido }} - {{ $pedido->cliente->nombre }}</p>
                        </div>
                        <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información del Pedido -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Información del Pedido</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cliente</label>
                            <p class="text-gray-900">{{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total</label>
                            <p class="text-gray-900">{{ $pedido->total_formateado }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pedido->estado_color }}">
                                {{ $pedido->estado }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Confirmación -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Confirmación de Recepción</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Confirma que el cliente ha recibido el pedido y envía notificaciones por WhatsApp y/o Email.
                    </p>
                </div>
                <div class="px-6 py-4">
                    <form action="{{ route('pedidos.procesar-confirmacion-recepcion', $pedido->id_pedido) }}" method="POST">
                        @csrf
                        
                        <!-- Observaciones -->
                        <div class="mb-6">
                            <label for="observaciones_recepcion" class="block text-sm font-medium text-gray-700 mb-2">
                                Observaciones (Opcional)
                            </label>
                            <textarea id="observaciones_recepcion" name="observaciones_recepcion" rows="4" 
                                      placeholder="Observaciones sobre la entrega, estado del producto, comentarios del cliente, etc."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('observaciones_recepcion') border-red-500 @enderror">{{ old('observaciones_recepcion') }}</textarea>
                            @error('observaciones_recepcion')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">Máximo 500 caracteres</p>
                        </div>

                        <!-- Notificación WhatsApp -->
                        <div class="mb-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" id="enviar_whatsapp" name="enviar_whatsapp" value="1" checked
                                           class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 focus:ring-2">
                                </div>
                                <div class="ml-3">
                                    <label for="enviar_whatsapp" class="text-sm font-medium text-gray-700">
                                        <i class="fab fa-whatsapp text-green-600 mr-1"></i>
                                        Enviar notificación por WhatsApp
                                    </label>
                                    <p class="text-sm text-gray-500">
                                        Se enviará un mensaje de confirmación al cliente
                                        @if($pedido->cliente->telefono)
                                            ({{ $pedido->cliente->telefono }})
                                        @else
                                            <span class="text-red-500">(Sin teléfono registrado)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            @if(!$pedido->cliente->telefono)
                                <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex">
                                        <i class="fas fa-exclamation-triangle text-yellow-400 mr-2 mt-0.5"></i>
                                        <div class="text-sm text-yellow-700">
                                            <strong>Advertencia:</strong> El cliente no tiene número de teléfono registrado. 
                                            No se podrá enviar la notificación por WhatsApp.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Notificación Email -->
                        <div class="mb-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" id="enviar_email" name="enviar_email" value="1" checked
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                </div>
                                <div class="ml-3">
                                    <label for="enviar_email" class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-envelope text-blue-600 mr-1"></i>
                                        Enviar notificación por Email
                                    </label>
                                    <p class="text-sm text-gray-500">
                                        Se enviará un email de confirmación de entrega al cliente
                                        @if($pedido->cliente->email)
                                            ({{ $pedido->cliente->email }})
                                        @else
                                            <span class="text-red-500">(Sin email registrado)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            @if(!$pedido->cliente->email)
                                <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex">
                                        <i class="fas fa-exclamation-triangle text-yellow-400 mr-2 mt-0.5"></i>
                                        <div class="text-sm text-yellow-700">
                                            <strong>Advertencia:</strong> El cliente no tiene email registrado. 
                                            No se podrá enviar la notificación por email.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Información del Registro -->
                        <div class="mb-6 p-4 bg-green-50 rounded-lg">
                            <h3 class="text-sm font-medium text-green-800 mb-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Información de la Confirmación
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-green-700">
                                <div>
                                    <i class="fas fa-user mr-1"></i>
                                    <strong>Confirmado por:</strong> {{ Auth::user()->nombre }}
                                </div>
                                <div>
                                    <i class="fas fa-calendar mr-1"></i>
                                    <strong>Fecha:</strong> {{ now('America/La_Paz')->format('d/m/Y H:i') }}
                                </div>
                                <div>
                                    <i class="fas fa-tag mr-1"></i>
                                    <strong>Nuevo estado:</strong> Entregado
                                </div>
                                <div>
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <strong>Acción:</strong> Confirmar recepción
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors">
                                <i class="fas fa-check mr-2"></i>
                                Confirmar Recepción
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
