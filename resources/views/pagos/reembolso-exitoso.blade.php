@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 flex items-center justify-center py-8">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <!-- Icono de éxito -->
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-green-600 text-3xl"></i>
            </div>
            
            <!-- Título -->
            @if(request('tipo') === 'pendiente')
                <h1 class="text-2xl font-bold text-gray-800 mb-4">
                    ¡Solicitud Registrada!
                </h1>
                
                <!-- Mensaje -->
                <p class="text-gray-600 mb-6">
                    La solicitud de reembolso por transferencia ha sido registrada exitosamente. Un administrador procesará la transferencia bancaria y te notificará cuando esté completada.
                </p>
            @else
                <h1 class="text-2xl font-bold text-gray-800 mb-4">
                    ¡Reembolso Completado!
                </h1>
                
                <!-- Mensaje -->
                <p class="text-gray-600 mb-6">
                    El reembolso en efectivo ha sido procesado exitosamente. Se ha registrado la anulación del pago y se han actualizado los registros correspondientes.
                </p>
            @endif
            
            <!-- Información del pedido -->
            @if(request('pedido'))
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="text-sm text-gray-600">Pedido</div>
                <div class="text-lg font-semibold text-gray-800">#{{ request('pedido') }}</div>
            </div>
            @endif
            
            <!-- Información adicional -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-2"></i>
                    <div class="text-sm text-blue-700 text-left">
                        @if(request('tipo') === 'pendiente')
                            <strong>Próximos pasos:</strong><br>
                            • Un administrador revisará la solicitud<br>
                            • Se procesará la transferencia bancaria<br>
                            • Recibirás notificación cuando esté completada<br>
                            • El pago se anulará una vez confirmada la transferencia
                        @else
                            <strong>Proceso completado:</strong><br>
                            • El pago ha sido anulado automáticamente<br>
                            • El cliente ha recibido el efectivo<br>
                            • Se ha generado el registro de anulación<br>
                            • El proceso está completamente finalizado
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="space-y-3">
                <a href="{{ route('pedidos.index') }}" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors inline-block">
                    <i class="fas fa-list mr-2"></i>
                    Volver a Pedidos
                </a>
                
                @if(request('pedido'))
                <a href="{{ route('pedidos.show', request('pedido')) }}" 
                   class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-6 rounded-lg transition-colors inline-block">
                    <i class="fas fa-eye mr-2"></i>
                    Ver Detalles del Pedido
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection