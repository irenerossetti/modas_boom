<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-boom-text-dark">Gestión de Roles</h1>
            <a href="{{ route('roles.create') }}" class="bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded-lg">
                Nuevo Rol
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
            <table class="w-full text-left">
                <thead class="text-boom-text-medium">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Descripción</th>
                        <th class="p-3">Estado</th>
                        <th class="p-3">Usuarios</th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-boom-cream-200">
                    @foreach ($roles as $rol)
                    <tr class="text-boom-text-dark">
                        <td class="p-3">{{ $rol->id_rol }}</td>
                        <td class="p-3 font-bold">{{ $rol->nombre }}</td>
                        <td class="p-3">{{ $rol->descripcion ?? 'Sin descripción' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $rol->habilitado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $rol->habilitado ? 'Habilitado' : 'Deshabilitado' }}
                            </span>
                        </td>
                        <td class="p-3">{{ $rol->usuarios->count() }}</td>
                        <td class="p-3">
                            <a href="{{ route('roles.show', $rol) }}" class="text-blue-500 hover:underline mr-2">Ver</a>
                            <a href="{{ route('roles.edit', $rol) }}" class="text-blue-500 hover:underline mr-2">Editar</a>
                            @if($rol->usuarios->count() == 0)
                                <form method="POST" action="{{ route('roles.destroy', $rol) }}" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este rol?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Eliminar</button>
                                </form>
                            @else
                                <span class="text-gray-400 text-sm">No eliminable</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>