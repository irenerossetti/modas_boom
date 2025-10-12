<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-history mr-2"></i>
                Historial de Pedidos
            </h1>
            <div class="flex space-x-2">
                <a href="{{ route('pedidos.create', ['cliente' => $cliente->id]) }}" 
                   class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-plus mr-1"></i>
                    Nuevo Pedido
                </a>
                <a href="{{ route('clientes.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Volver a Clientes
                </a>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-boom-primary rounded-full flex items-center justify-center text-white text-2xl font-bold mr-6">
                    {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-boom-text-dark">
                        {{ $cliente->nombre }} {{ $cliente->apellido }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 text-sm">
                        <div>
                            <span class="text-gray-500">CI/NIT:</span>
                            <span class="font-medium ml-2">{{ $cliente->ci_nit }}</span>
                        </div>
                        @if($cliente->email)
                        <div>
                            <span class="text-gray-500">Email:</span>
                            <span class="font-medium ml-2">{{ $cliente->email }}</span>
                        </div>
                        @endif
                        @if($cliente->telefono)
                        <div>
                            <span class="text-gray-500">Teléfono:</span>
                            <span class="font-medium ml-2">{{ $cliente->telefono }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-boom-primary">
                        {{ $pedidos->total() }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $pedidos->total() == 1 ? 'Pedido' : 'Pedidos' }} Total{{ $pedidos->total() == 1 ? '' : 'es' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        @if($pedidos->total() > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            @php
                $estadisticas = [
                    'En proceso' => $pedidos->where('estado', 'En proceso')->count(),
                    'Asignado' => $pedidos->where('estado', 'Asignado')->count(),
                    'En producción' => $pedidos->where('estado', 'En producción')->count(),
                    'Terminado' => $pedidos->where('estado', 'Terminado')->count(),
                    'Entregado' => $pedidos->where('estado', 'Entregado')->count(),
                    'Cancelado' => $pedidos->where('estado', 'Cancelado')->count(),
                ];
                $colores = [
                    'En proceso' => 'bg-blue-500',
                    'Asignado' => 'bg-yellow-500',
                    'En producción' => 'bg-orange-500',
                    'Terminado' => 'bg-green-500',
                    'Entregado' => 'bg-purple-500',
                    'Cancelado' => 'bg-red-500',
                ];
            @endphp
            
            @foreach(['En proceso', 'Terminado', 'Entregado', 'Cancelado'] as $estado)
                @if($estadisticas[$estado] > 0)
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="w-3 h-3 {{ $colores[$estado] }} rounded-full mr-3"></div>
                        <div>
                            <div class="text-2xl font-bold text-boom-text-dark">{{ $estadisticas[$estado] }}</div>
                            <div class="text-sm text-gray-500">{{ $estado }}</div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        <!-- Lista de Pedidos -->
        <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-boom-text-dark">
                    Pedidos del Cliente
                    <span class="text-sm text-gray-600">({{ $pedidos->total() }} pedidos)</span>
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-boom-cream-200 text-boom-text-dark">
                        <tr>
                            <th class="p-4 font-semibold"># Pedido</th>
                            <th class="p-4 font-semibold">Estado</th>
                            <th class="p-4 font-semibold">Total</th>
                            <th class="p-4 font-semibold">Fecha</th>
                            <th class="p-4 font-semibold text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-boom-cream-200">
                        @forelse ($pedidos as $pedido)
                        <tr class="text-boom-text-dark hover:bg-boom-cream-50 border-b border-boom-cream-200">
                            <td class="p-4">
                                <div class="font-bold text-boom-primary">
                                    #{{ $pedido->id_pedido }}
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $pedido->estado_color }}">
                                    <i class="{{ $pedido->estado_icono }} mr-1"></i>
                                    {{ $pedido->estado }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-boom-text-dark">
                                    {{ $pedido->total_formateado }}
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm">
                                    <div class="font-semibold text-boom-text-dark">
                                        {{ $pedido->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $pedido->created_at->format('H:i') }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ $pedido->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($pedido->puedeSerEditado())
                                        <a href="{{ route('pedidos.edit', $pedido->id_pedido) }}" 
                                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route('pedidos.historial', $pedido->id_pedido) }}" 
                                       class="bg-purple-500 hover:bg-purple-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200"
                                       title="Ver historial">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">
                                <i class="fas fa-shopping-bag text-3xl mb-2"></i><br>
                                Este cliente aún no tiene pedidos registrados
                                <div class="mt-3">
                                    <a href="{{ route('pedidos.create', ['cliente' => $cliente->id]) }}" 
                                       class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                        <i class="fas fa-plus mr-1"></i>
                                        Crear Primer Pedido
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($pedidos->hasPages())
                <div class="mt-4">
                    {{ $pedidos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>