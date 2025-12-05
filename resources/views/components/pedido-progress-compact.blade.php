@props(['estado'])

@php
    // Mapeo de estados a números de paso
    $pasos = [
        'En proceso' => 1,
        'Asignado' => 2,
        'En producción' => 3,
        'Terminado' => 4,
        'Entregado' => 5,
    ];
    
    $pasoActual = $pasos[$estado] ?? 0;
    $porcentaje = $pasoActual > 0 ? (($pasoActual - 1) / 4) * 100 : 0;
@endphp

<div class="w-full">
    <!-- Barra de progreso simple -->
    <div class="flex items-center space-x-2">
        <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
            <div class="bg-gradient-to-r from-green-400 to-green-600 h-full rounded-full transition-all duration-500 ease-out"
                 style="width: {{ $porcentaje }}%">
            </div>
        </div>
        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">
            {{ $pasoActual }}/5
        </span>
    </div>
    
    <!-- Estado actual -->
    <div class="mt-1">
        <span class="text-xs font-medium
            @if($estado == 'En proceso') text-yellow-600
            @elseif($estado == 'Asignado') text-blue-600
            @elseif($estado == 'En producción') text-purple-600
            @elseif($estado == 'Terminado') text-green-600
            @elseif($estado == 'Entregado') text-green-700
            @else text-gray-600 @endif">
            {{ $estado }}
        </span>
    </div>
</div>
