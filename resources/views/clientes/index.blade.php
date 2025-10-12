<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-boom-text-dark">Gestión de Clientes</h1>
            @if(Auth::user()->id_rol == 1)
                <a href="{{ route('clientes.create') }}" class="bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded">
                    Nuevo Cliente
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <!-- Formulario de búsqueda -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form method="GET" action="{{ route('clientes.index') }}" class="flex gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar por nombre, apellido o CI/NIT</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           class="form-input block w-full rounded-md shadow-sm"
                           placeholder="Ingrese nombre, apellido o CI/NIT...">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded">
                        Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('clientes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
            <table class="w-full text-left">
                <thead class="text-boom-text-medium">
                    <tr>
                        <th class="p-3">Num</th>
                        <th class="p-3">Nombre Completo</th>
                        <th class="p-3">CI/NIT</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Teléfono</th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-boom-cream-200">
                    @forelse ($clientes as $cliente)
                    <tr class="text-boom-text-dark">
                        <td class="p-3">{{ ($clientes->currentPage() - 1) * $clientes->perPage() + $loop->iteration }}</td>
                        <td class="p-3 font-bold">{{ $cliente->nombre }} {{ $cliente->apellido }}</td>
                        <td class="p-3">{{ $cliente->ci_nit }}</td>
                        <td class="p-3">{{ $cliente->email ?? 'N/A' }}</td>
                        <td class="p-3">{{ $cliente->telefono ?? 'N/A' }}</td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2">
                                <!-- Botón para crear pedido - disponible para todos -->
                                <a href="{{ route('pedidos.create', ['cliente' => $cliente->id]) }}" 
                                   class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200"
                                   title="Crear pedido para este cliente">
                                    <i class="fas fa-shopping-bag mr-1"></i>
                                    Nuevo Pedido
                                </a>
                                
                                <!-- Botón para ver historial de pedidos -->
                                <a href="{{ route('pedidos.cliente-historial', $cliente->id) }}" 
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200"
                                   title="Ver historial de pedidos">
                                    <i class="fas fa-history mr-1"></i>
                                    Historial
                                </a>
                                
                                @if(Auth::user()->id_rol == 1)
                                    <!-- Botones de administrador -->
                                    <a href="{{ route('clientes.edit', $cliente) }}" 
                                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200"
                                       title="Editar cliente">
                                        <i class="fas fa-edit mr-1"></i>
                                        Editar
                                    </a>
                                    <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('¿Está seguro de que desea eliminar este cliente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200"
                                                title="Eliminar cliente">
                                            <i class="fas fa-trash mr-1"></i>
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-3 text-center text-gray-500">No hay clientes registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Paginación -->
            @if($clientes->hasPages())
                <div class="mt-4">
                    {{ $clientes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>