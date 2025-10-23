<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-boom-text-dark">
                            <i class="fas fa-users mr-2"></i>
                            Pedidos por Operario
                        </h2>
                        <a href="{{ route('pedidos.index') }}" class="bg-boom-cream-200 hover:bg-boom-cream-300 text-boom-text-dark font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>Volver a Pedidos
                        </a>
                    </div>

                    <!-- Formulario de selección de operario -->
                    <div class="bg-boom-cream-100 rounded-lg p-4 mb-6">
                        <form method="GET" action="{{ route('pedidos.por-operario') }}" class="flex items-end space-x-4">
                            <div class="flex-1">
                                <label for="id_operario" class="block text-sm font-medium text-boom-text-dark mb-2">
                                    Seleccionar Operario
                                </label>
                                <select name="id_operario" id="id_operario" 
                                        class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-rose-dark">
                                    <option value="">Seleccione un operario...</option>
                                    @foreach($operarios as $operario)
                                        <option value="{{ $operario->id_usuario }}" 
                                                {{ request('id_operario') == $operario->id_usuario ? 'selected' : '' }}>
                                            {{ $operario->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                <i class="fas fa-search mr-2"></i>Consultar
                            </button>
                        </form>
                    </div>

                    @if($operarioSeleccionado)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold text-blue-800 mb-2">
                                <i class="fas fa-user mr-2"></i>
                                Pedidos de {{ $operarioSeleccionado->nombre }}
                            </h3>
                            <p class="text-blue-700">
                                Mostrando pedidos asignados a este operario
                            </p>
                        </div>

                        @if($pedidos->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full bg-white rounded-lg shadow">
                                    <thead class="bg-boom-cream-200">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                                # Pedido
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                                Cliente
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                                Estado
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                                Total
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                                Fecha
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-boom-text-dark uppercase tracking-wider">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($pedidos as $pedido)
                                            <tr class="hover:bg-boom-cream-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-bold text-boom-rose-dark">
                                                        #{{ $pedido->id_pedido }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="w-8 h-8 bg-boom-rose-dark rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                                                            {{ strtoupper(substr($pedido->cliente->nombre, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-boom-text-dark">
                                                                {{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                CI: {{ $pedido->cliente->ci_nit }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($pedido->estado == 'Asignado') bg-blue-100 text-blue-800
                                                        @elseif($pedido->estado == 'En producción') bg-purple-100 text-purple-800
                                                        @elseif($pedido->estado == 'Terminado') bg-green-100 text-green-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ $pedido->estado }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-semibold text-boom-text-dark">
                                                        @if($pedido->total)
                                                            Bs. {{ number_format($pedido->total, 2) }}
                                                        @else
                                                            Por definir
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-boom-text-dark">
                                                        {{ $pedido->created_at->format('d/m/Y') }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $pedido->created_at->format('H:i') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                                                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition-colors duration-200">
                                                            <i class="fas fa-eye mr-1"></i>Ver
                                                        </a>
                                                        @if($pedido->puedeSerEditado())
                                                            <a href="{{ route('pedidos.edit', $pedido->id_pedido) }}" 
                                                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs transition-colors duration-200">
                                                                <i class="fas fa-edit mr-1"></i>Editar
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            @if($pedidos->hasPages())
                                <div class="mt-6">
                                    {{ $pedidos->appends(request()->query())->links() }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-12">
                                <div class="bg-boom-cream-100 rounded-lg p-8">
                                    <i class="fas fa-clipboard-list text-6xl text-boom-text-medium mb-4"></i>
                                    <h3 class="text-xl font-semibold text-boom-text-dark mb-2">Sin pedidos asignados</h3>
                                    <p class="text-boom-text-medium mb-6">
                                        {{ $operarioSeleccionado->nombre }} no tiene pedidos asignados actualmente.
                                    </p>
                                    <a href="{{ route('pedidos.index') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                        <i class="fas fa-arrow-left mr-2"></i>Ver Todos los Pedidos
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <div class="bg-boom-cream-100 rounded-lg p-8">
                                <i class="fas fa-user-friends text-6xl text-boom-text-medium mb-4"></i>
                                <h3 class="text-xl font-semibold text-boom-text-dark mb-2">Consulta de Pedidos por Operario</h3>
                                <p class="text-boom-text-medium mb-6">
                                    Selecciona un operario para ver los pedidos que tiene asignados.
                                </p>
                                <p class="text-sm text-boom-text-medium">
                                    Esta funcionalidad te permite hacer seguimiento del trabajo asignado a cada operario.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>