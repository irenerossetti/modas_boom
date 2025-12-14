@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-rose-100 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <!-- Icono de error -->
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100">
                    <i class="fas fa-times text-3xl text-red-600"></i>
                </div>
            </div>
            
            <!-- Título -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                Error al Procesar el Pago
            </h1>
            
            <!-- Mensaje de error -->
            <p class="text-lg text-gray-600 mb-6">
                @if(session('error_mensaje'))
                    {{ session('error_mensaje') }}
                @else
                    No se pudo procesar tu pago. Por favor, intenta nuevamente.
                @endif
            </p>
            
            <!-- Detalles del error -->
            @if(session('error_detalles'))
                <div class="bg-red-50 rounded-lg p-6 mb-6 text-left">
                    <h3 class="text-lg font-semibold text-red-800 mb-4">Detalles del Error:</h3>
                    <div class="text-sm text-red-700">
                        @if(is_array(session('error_detalles')))
                            @foreach(session('error_detalles') as $key => $value)
                                <p><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</p>
                            @endforeach
                        @else
                            <p>{{ session('error_detalles') }}</p>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Botones de acción -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('pagos.pasarela') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                    <i class="fas fa-redo mr-2"></i>
                    Intentar Nuevamente
                </a>
                
                <a href="{{ route('dashboard') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i>
                    Ir al Dashboard
                </a>
            </div>
            
            <!-- Información de contacto -->
            <div class="mt-8 p-4 bg-yellow-50 rounded-lg">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-phone mr-2"></i>
                    Si el problema persiste, contacta con nuestro soporte técnico.
                </p>
            </div>
            
            <!-- Posibles soluciones -->
            <div class="mt-6 text-left">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">Posibles soluciones:</h4>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                        Verifica que todos los datos estén correctos
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                        Asegúrate de tener conexión a internet estable
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                        Si usas tarjeta, verifica que tenga fondos suficientes
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                        Intenta con un método de pago diferente
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si hay mensaje de error en sessionStorage
    const errorMensaje = sessionStorage.getItem('error_mensaje');
    if (errorMensaje) {
        // Actualizar el mensaje de error en la página
        const mensajeElement = document.querySelector('.text-lg.text-gray-600');
        if (mensajeElement) {
            mensajeElement.textContent = errorMensaje;
        }
        
        // Limpiar sessionStorage
        sessionStorage.removeItem('error_mensaje');
    }
});
</script>