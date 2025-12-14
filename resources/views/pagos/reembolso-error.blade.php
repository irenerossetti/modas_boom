@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-pink-100 flex items-center justify-center py-8">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <!-- Icono de error -->
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-times text-red-600 text-3xl"></i>
            </div>
            
            <!-- Título -->
            <h1 class="text-2xl font-bold text-gray-800 mb-4">
                Error en el Reembolso
            </h1>
            
            <!-- Mensaje -->
            <p class="text-gray-600 mb-6">
                {{ session('error_message', 'Ocurrió un error al procesar el reembolso. Por favor, inténtalo nuevamente o contacta al administrador.') }}
            </p>
            
            <!-- Información del pedido -->
            @if(request('pedido'))
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="text-sm text-gray-600">Pedido</div>
                <div class="text-lg font-semibold text-gray-800">#{{ request('pedido') }}</div>
            </div>
            @endif
            
            <!-- Información de ayuda -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-2"></i>
                    <div class="text-sm text-yellow-700 text-left">
                        <strong>¿Necesitas ayuda?</strong><br>
                        • Verifica que el pago no haya sido anulado previamente<br>
                        • Contacta al administrador del sistema<br>
                        • Revisa los datos ingresados
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="space-y-3">
                @if(request('pedido'))
                <a href="{{ route('pagos.reembolso', request('pedido')) }}" 
                   class="w-full bg-orange-600 hover:bg-orange-700 text-white font-medium py-3 px-6 rounded-lg transition-colors inline-block">
                    <i class="fas fa-redo mr-2"></i>
                    Intentar Nuevamente
                </a>
                @endif
                
                <a href="{{ route('pedidos.index') }}" 
                   class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-6 rounded-lg transition-colors inline-block">
                    <i class="fas fa-list mr-2"></i>
                    Volver a Pedidos
                </a>
            </div>
        </div>
    </div>
</div>
@endsection