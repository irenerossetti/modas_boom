@extends('layouts.app')

@section('content')
    <div class="py-4 lg:py-12">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl rounded-lg">
                <div class="p-3 sm:p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 lg:mb-6 space-y-3 sm:space-y-0">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-boom-text-dark">
                                <i class="fas fa-calculator mr-2"></i>
                                Presupuestos de Producción
                            </h2>
                            <p class="text-sm text-boom-text-medium mt-1">Gestión de costos de confección</p>
                        </div>
                        <a href="{{ route('presupuestos-produccion.create') }}" 
                           class="bg-white hover:bg-gray-50 text-black font-semibold py-2 px-3 sm:px-4 rounded-lg border-2 border-gray-800 hover:border-gray-600 transition-colors duration-300 text-sm sm:text-base text-center">
                            <i class="fas fa-plus mr-1 sm:mr-2 text-black"></i>
                            <span class="hidden sm:inline">Nuevo Presupuesto</span>
                            <span class="sm:hidden">Nuevo</span>
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filtros -->
                    <div class="bg-boom-cream-100 rounded-lg p-3 sm:p-4 mb-4 lg:mb-6">
                        <form method="GET" action="{{ route('presupuestos-produccion.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                            <div>
                                <label for="estado" class="block text-sm font-medium text-boom-text-dark mb-1">Estado</label>
                                <select name="estado" id="estado" class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary">
                                    <option value="">Todos los estados</option>
                                    @foreach($estados as $estado)
                                        <option value="{{ $estado }}" {{ (request('estado') == $estado) ? 'selected' : '' }}>
                                            {{ $estado }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="tipo_prenda" class="block text-sm font-medium text-boom-text-dark mb-1">Tipo de Prenda</label>
                                <input type="text" name="tipo_prenda" id="tipo_prenda" value="{{ request('tipo_prenda') }}" 
                                       placeholder="Buscar por tipo..."
                                       class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary">
                            </div>
                            <div>
                                <label for="fecha_desde" class="block text-sm font-medium text-boom-text-dark mb-1">Desde</label>
                                <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" 
                                       class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary">
                            </div>
                            <div>
                                <label for="fecha_hasta" class="block text-sm font-medium text-boom-text-dark mb-1">Hasta</label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" 
                                       class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary">
                            </div>
                            <div class="flex items-end space-x-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg border-2 border-blue-800 transition-colors duration-300">
                                    <i class="fas fa-search mr-1 text-white"></i>Filtrar
                                </button>
                                <a href="{{ route('presupuestos-produccion.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                    <i class="fas fa-times mr-1"></i>Limpiar
                                </a>
                            </div>
                        </form>
                    </div>

                    @if($presupuestos->count() > 0)
                        <!-- Lista de Presupuestos -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-boom-cream-200">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                            Presupuesto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                            Tipo de Prenda
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                            Tipo de Tela
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                            Costo Total
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($presupuestos as $presupuesto)
                                        <tr class="hover:bg-boom-cream-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-boom-text-dark">
                                                    #{{ $presupuesto->id }}
                                                </div>
                                                <div class="text-sm text-boom-text-medium">
                                                    Por: {{ $presupuesto->usuarioRegistro->nombre ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-boom-text-dark">
                                                    {{ $presupuesto->tipo_prenda }}
                                                </div>
                                                @if($presupuesto->pedido)
                                                    <div class="text-xs text-blue-600">
                                                        <i class="fas fa-link mr-1"></i>
                                                        Pedido #{{ $presupuesto->pedido->id_pedido }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-boom-text-medium">
                                                {{ $presupuesto->tipo_tela }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-lg font-bold text-boom-primary">
                                                    {{ $presupuesto->costo_total_formateado }}
                                                </div>
                                                <div class="text-xs text-boom-text-medium">
                                                    Mat: {{ $presupuesto->total_materiales_formateado }} | 
                                                    M.O: {{ $presupuesto->total_mano_obra_formateado }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($presupuesto->estado == 'Borrador') bg-yellow-100 text-yellow-800
                                                    @elseif($presupuesto->estado == 'Aprobado') bg-green-100 text-green-800
                                                    @elseif($presupuesto->estado == 'Utilizado') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ $presupuesto->estado }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-boom-text-medium">
                                                {{ $presupuesto->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('presupuestos-produccion.show', $presupuesto->id) }}" 
                                                       class="text-boom-primary hover:text-boom-primary-dark">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($presupuesto->puedeSerModificado())
                                                        <a href="{{ route('presupuestos-produccion.edit', $presupuesto->id) }}" 
                                                           class="text-blue-600 hover:text-blue-900">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('presupuestos-produccion.duplicar', $presupuesto->id) }}" 
                                                       class="text-green-600 hover:text-green-900"
                                                       title="Duplicar presupuesto">
                                                        <i class="fas fa-copy"></i>
                                                    </a>
                                                    @if(Auth::user()->id_rol == 1 && $presupuesto->estado == 'Borrador')
                                                        <form method="POST" action="{{ route('presupuestos-produccion.destroy', $presupuesto->id) }}" 
                                                              class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este presupuesto?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-6">
                            {{ $presupuestos->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="bg-boom-cream-100 rounded-lg p-8">
                                <i class="fas fa-calculator text-6xl text-boom-text-medium mb-4"></i>
                                <h3 class="text-xl font-semibold text-boom-text-dark mb-2">No hay presupuestos registrados</h3>
                                <p class="text-boom-text-medium mb-6">
                                    @if(request()->hasAny(['estado', 'tipo_prenda', 'fecha_desde', 'fecha_hasta']))
                                        No hay presupuestos que coincidan con los filtros seleccionados.
                                    @else
                                        Comienza creando tu primer presupuesto de producción.
                                    @endif
                                </p>
                                @if(request()->hasAny(['estado', 'tipo_prenda', 'fecha_desde', 'fecha_hasta']))
                                    <a href="{{ route('presupuestos-produccion.index') }}" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-semibold py-2 px-4 rounded-lg transition-colors duration-300 mr-4">
                                        <i class="fas fa-times mr-2"></i>Limpiar Filtros
                                    </a>
                                @endif
                                <a href="{{ route('presupuestos-produccion.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg border-2 border-green-800 transition-colors duration-300">
                                    <i class="fas fa-plus mr-2 text-white"></i>Crear Presupuesto
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection