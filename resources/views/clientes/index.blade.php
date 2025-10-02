<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-boom-text-dark">Gestión de Clientes</h1>
            <a href="{{ route('clientes.create') }}" class="bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded">
                Nuevo Cliente
            </a>
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
                        <th class="p-3">ID</th>
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
                        <td class="p-3">{{ $cliente->id }}</td>
                        <td class="p-3 font-bold">{{ $cliente->nombre }} {{ $cliente->apellido }}</td>
                        <td class="p-3">{{ $cliente->ci_nit }}</td>
                        <td class="p-3">{{ $cliente->email ?? 'N/A' }}</td>
                        <td class="p-3">{{ $cliente->telefono ?? 'N/A' }}</td>
                        <td class="p-3">
                            <a href="{{ route('clientes.edit', $cliente) }}" class="text-blue-500 hover:underline">Editar</a>
                            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline ml-4" onsubmit="return confirm('¿Está seguro de que desea eliminar este cliente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Eliminar</button>
                            </form>
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