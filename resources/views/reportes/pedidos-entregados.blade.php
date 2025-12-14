@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-boom-text-dark">Reporte de Pedidos Entregados</h2>
                            <p class="text-sm text-boom-text-medium mt-1">Análisis de pedidos completados y calificaciones</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('reportes.pedidos-entregados.pdf') }}?{{ http_build_query(request()->query()) }}" 
                               class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                <i class="fas fa-file-pdf mr-2"></i>PDF
                            </a>
                            <a href="{{ route('reportes.pedidos-entregados.csv') }}?{{ http_build_query(request()->query()) }}" 
                               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                <i class="fas fa-file-csv mr-2"></i>CSV
                            </a>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="bg-boom-cream-100 p-4 rounded-lg mb-6">
                        <form method="GET" action="{{ route('reportes.pedidos-entregados') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label for="fecha_desde" class="block text-sm font-medium text-boom-text-dark mb-1">Desde</label>
                                <input type="date" name="fecha_desde" id="fecha_desde" value="{{ $fechaDesde }}" 
                                       class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="fecha_hasta" class="block text-sm font-medium text-boom-text-dark mb-1">Hasta</label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ $fechaHasta }}" 
                                       class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="con_calificacion" class="block text-sm font-medium text-boom-text-dark mb-1">Calificación</label>
                                <select name="con_calificacion" id="con_calificacion" 
                                        class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                    <option value="">Todos</option>
                                    <option value="si" {{ $conCalificacion === 'si' ? 'selected' : '' }}>Con calificación</option>
                                    <option value="no" {{ $conCalificacion === 'no' ? 'selected' : '' }}>Sin calificación</option>
                                </select>
                            </div>
                            <div>
                                <label for="calificacion_min" class="block text-sm font-medium text-boom-text-dark mb-1">Calificación mínima</label>
                                <select name="calificacion_min" id="calificacion_min" 
                                        class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                    <option value="">Todas</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ $calificacionMin == $i ? 'selected' : '' }}>{{ $i }} estrella{{ $i > 1 ? 's' : '' }} o más</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="flex items-end space-x-2">
                                <button type="submit" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                    <i class="fas fa-search mr-1"></i>Filtrar
                                </button>
                                <a href="{{ route('reportes.pedidos-entregados') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                    <i class="fas fa-times mr-1"></i>Limpiar
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Estadísticas -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $estadisticas['total_entregados'] }}</div>
                            <div class="text-sm text-blue-800">Total Entregados</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $estadisticas['con_calificacion'] }}</div>
                            <div class="text-sm text-green-800">Con Calificación</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">
                                {{ $estadisticas['promedio_calificacion'] ? number_format($estadisticas['promedio_calificacion'], 1) : 'N/A' }}
                            </div>
                            <div class="text-sm text-yellow-800">Promedio Calificación</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ $estadisticas['total_entregados'] > 0 ? number_format(($estadisticas['con_calificacion'] / $estadisticas['total_entregados']) * 100, 1) : 0 }}%
                            </div>
                            <div class="text-sm text-purple-800">% Calificados</div>
                        </div>
                    </div>

                    <!-- Distribución de Calificaciones -->
                    @if($estadisticas['por_calificacion']->count() > 0)
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-3">Distribución de Calificaciones</h3>
                            <div class="grid grid-cols-5 gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @php
                                        $calificacion = $estadisticas['por_calificacion']->where('calificacion', $i)->first();
                                        $total = $calificacion ? $calificacion->total : 0;
                                    @endphp
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-yellow-600">{{ $total }}</div>
                                        <div class="flex justify-center mb-1">
                                            @for($j = 1; $j <= $i; $j++)
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                            @endfor
                                        </div>
                                        <div class="text-xs text-gray-600">{{ $i }} estrella{{ $i > 1 ? 's' : '' }}</div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endif

                    <!-- Lista de Pedidos -->
                    @if($pedidos->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Entrega</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calificación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pedidos as $pedido)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">#{{ $pedido->id_pedido }}</div>
                                                <div class="text-sm text-gray-500">{{ $pedido->created_at->format('d/m/Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}</div>
                                                <div class="text-sm text-gray-500">{{ $pedido->cliente->ci_nit }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $pedido->total_formateado }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $pedido->updated_at->format('d/m/Y H:i') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($pedido->yaFueCalificado())
                                                    <div class="flex items-center space-x-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fas fa-star text-sm {{ $i <= $pedido->calificacion ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                                        @endfor
                                                        <span class="text-sm font-medium text-gray-900 ml-2">{{ $pedido->calificacion }}/5</span>
                                                    </div>
                                                    <div class="text-xs text-gray-500">{{ $pedido->fecha_calificacion->format('d/m/Y') }}</div>
                                                    @if($pedido->comentario_calificacion)
                                                        <div class="text-xs text-gray-600 mt-1 italic max-w-xs truncate" title="{{ $pedido->comentario_calificacion }}">
                                                            "{{ Str::limit($pedido->comentario_calificacion, 50) }}"
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-sm text-gray-500">Sin calificar</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                                                   class="text-boom-rose-dark hover:text-boom-rose-light">
                                                    <i class="fas fa-eye mr-1"></i>Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-6">
                            {{ $pedidos->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-chart-bar text-6xl text-boom-text-light mb-4"></i>
                            <h3 class="text-xl font-semibold text-boom-text-dark mb-2">No hay pedidos entregados</h3>
                            <p class="text-boom-text-medium">No se encontraron pedidos entregados con los filtros seleccionados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection