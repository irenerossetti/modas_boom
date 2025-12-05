@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-chart-line mr-2"></i>
                Reporte de Rendimiento por Operario
            </h1>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('reportes.produccion.index') }}" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                
                <!-- Exportar PDF -->
                <form method="GET" action="{{ route('reportes.produccion.exportar-pdf') }}" class="inline">
                    <input type="hidden" name="operario_id" value="{{ request('operario_id') }}">
                    <input type="hidden" name="fecha_desde" value="{{ $fecha_desde }}">
                    <input type="hidden" name="fecha_hasta" value="{{ $fecha_hasta }}">
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        <i class="fas fa-file-pdf mr-2"></i> PDF
                    </button>
                </form>
                
                <!-- Exportar Excel -->
                <form method="GET" action="{{ route('reportes.produccion.exportar-excel') }}" class="inline">
                    <input type="hidden" name="operario_id" value="{{ request('operario_id') }}">
                    <input type="hidden" name="fecha_desde" value="{{ $fecha_desde }}">
                    <input type="hidden" name="fecha_hasta" value="{{ $fecha_hasta }}">
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        <i class="fas fa-file-excel mr-2"></i> Excel
                    </button>
                </form>
                
                <!-- Exportar CSV -->
                <form method="GET" action="{{ route('reportes.produccion.exportar-csv') }}" class="inline">
                    <input type="hidden" name="operario_id" value="{{ request('operario_id') }}">
                    <input type="hidden" name="fecha_desde" value="{{ $fecha_desde }}">
                    <input type="hidden" name="fecha_hasta" value="{{ $fecha_hasta }}">
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        <i class="fas fa-file-csv mr-2"></i> CSV
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filtros Aplicados -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-900 mb-2">📊 Filtros Aplicados</h3>
                <div class="text-sm text-blue-800">
                    <p><strong>Operario:</strong> {{ $operarioSeleccionado ? $operarioSeleccionado->nombre : 'Todos' }}</p>
                    <p><strong>Período:</strong> 
                        @if($fecha_desde && $fecha_hasta)
                            {{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}
                        @elseif($fecha_desde)
                            Desde {{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }}
                        @elseif($fecha_hasta)
                            Hasta {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}
                        @else
                            Todos los registros
                        @endif
                    </p>
                </div>
            </div>

            <!-- Resumen General -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📈 Resumen General</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Avances</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $totales['total_avances'] }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Prendas</p>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($totales['total_prendas']) }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total a Pagar</p>
                            <p class="text-2xl font-bold text-yellow-600">Bs. {{ number_format($totales['total_a_pagar'], 2) }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Promedio por Avance</p>
                            <p class="text-2xl font-bold text-purple-600">Bs. {{ number_format($totales['promedio_general'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas por Operario -->
            @if($estadisticas->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">👷 Rendimiento por Operario</h3>
                        
                        @foreach($estadisticas as $stat)
                            <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            {{ $stat['operario']->nombre }}
                                        </h4>
                                        <p class="text-sm text-gray-600">{{ $stat['operario']->email }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-green-600">
                                            Bs. {{ number_format($stat['total_a_pagar'], 2) }}
                                        </p>
                                        <p class="text-xs text-gray-500">Total a pagar</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                                    <div class="bg-gray-50 p-3 rounded">
                                        <p class="text-xs text-gray-600">Avances Registrados</p>
                                        <p class="text-lg font-semibold">{{ $stat['total_avances'] }}</p>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded">
                                        <p class="text-xs text-gray-600">Prendas Procesadas</p>
                                        <p class="text-lg font-semibold">{{ number_format($stat['total_prendas_procesadas']) }}</p>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded">
                                        <p class="text-xs text-gray-600">Promedio por Avance</p>
                                        <p class="text-lg font-semibold">Bs. {{ number_format($stat['promedio_por_avance'], 2) }}</p>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded">
                                        <p class="text-xs text-gray-600">Etapas Trabajadas</p>
                                        <p class="text-lg font-semibold">{{ $stat['etapas']->count() }}</p>
                                    </div>
                                </div>

                                <div class="text-sm text-gray-600">
                                    <strong>Etapas:</strong> {{ $stat['etapas']->implode(', ') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Detalle de Avances -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">📋 Detalle de Avances</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedido</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Etapa</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Costo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($avances as $avance)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $avance->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $avance->operario->nombre ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('pedidos.show', $avance->id_pedido) }}" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                    #{{ $avance->id_pedido }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $avance->etapa }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $avance->porcentaje_avance }}%
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                                Bs. {{ number_format($avance->costo_mano_obra, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <p class="text-yellow-800">⚠️ No se encontraron registros con los filtros aplicados.</p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
