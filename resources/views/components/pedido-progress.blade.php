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
    
    // Configuración de cada paso
    $steps = [
        [
            'numero' => 1,
            'nombre' => 'En proceso',
            'icono' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>'
        ],
        [
            'numero' => 2,
            'nombre' => 'Asignado',
            'icono' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>'
        ],
        [
            'numero' => 3,
            'nombre' => 'En producción',
            'icono' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'
        ],
        [
            'numero' => 4,
            'nombre' => 'Terminado',
            'icono' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        ],
        [
            'numero' => 5,
            'nombre' => 'Entregado',
            'icono' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>'
        ],
    ];
@endphp

<div class="w-full py-2">
    <!-- Versión Desktop -->
    <div class="hidden sm:block">
        <div class="flex items-center justify-between relative">
            @foreach($steps as $index => $step)
                @php
                    $isCompleted = $step['numero'] <= $pasoActual;
                    $isCurrent = $step['numero'] == $pasoActual;
                @endphp
                
                <!-- Paso -->
                <div class="flex flex-col items-center relative z-10 flex-1">
                    <!-- Icono -->
                    <div class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-300 
                        {{ $isCompleted ? 'bg-green-500 text-white shadow-lg' : 'bg-gray-200 text-gray-400' }}
                        {{ $isCurrent ? 'ring-4 ring-green-200 scale-110' : '' }}">
                        {!! $step['icono'] !!}
                    </div>
                    
                    <!-- Nombre del paso -->
                    <span class="text-xs mt-2 font-medium text-center
                        {{ $isCompleted ? 'text-green-600' : 'text-gray-400' }}
                        {{ $isCurrent ? 'font-bold' : '' }}">
                        {{ $step['nombre'] }}
                    </span>
                </div>
                
                <!-- Línea conectora (no mostrar después del último paso) -->
                @if(!$loop->last)
                    <div class="flex-1 h-1 mx-2 -mt-8 transition-all duration-300
                        {{ $step['numero'] < $pasoActual ? 'bg-green-500' : 'bg-gray-200' }}">
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    
    <!-- Versión Mobile -->
    <div class="block sm:hidden">
        <div class="flex items-center space-x-2">
            @foreach($steps as $index => $step)
                @php
                    $isCompleted = $step['numero'] <= $pasoActual;
                    $isCurrent = $step['numero'] == $pasoActual;
                @endphp
                
                <!-- Icono compacto -->
                <div class="flex items-center justify-center w-8 h-8 rounded-full transition-all duration-300 
                    {{ $isCompleted ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400' }}
                    {{ $isCurrent ? 'ring-2 ring-green-300' : '' }}">
                    {!! $step['icono'] !!}
                </div>
                
                <!-- Línea conectora -->
                @if(!$loop->last)
                    <div class="flex-1 h-0.5 transition-all duration-300
                        {{ $step['numero'] < $pasoActual ? 'bg-green-500' : 'bg-gray-200' }}">
                    </div>
                @endif
            @endforeach
        </div>
        
        <!-- Estado actual en mobile -->
        <div class="mt-2 text-center">
            <span class="text-xs font-medium
                {{ $pasoActual > 0 ? 'text-green-600' : 'text-gray-400' }}">
                {{ $estado }}
            </span>
        </div>
    </div>
</div>
