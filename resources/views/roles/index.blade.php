<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-boom-text-dark">Gestión de Roles</h1>
                <p class="text-sm text-boom-text-medium mt-1">Administra los roles y permisos del sistema</p>
            </div>
            @if(!isset($isReadOnly) || !$isReadOnly)
                <a href="{{ route('roles.create') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                    <i class="fas fa-plus mr-2"></i>Nuevo Rol
                </a>
            @else
                <span class="bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-eye mr-2"></i>Solo Lectura
                </span>
            @endif
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
                        <th class="p-3">Num</th>
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
                        <td class="p-3">{{ $loop->iteration }}</td>
                        <td class="p-3 font-bold">
                            {{ $rol->nombre }}
                            @if($rol->nombre === 'Administrador' || $rol->id_rol == 1)
                                <span class="ml-2 px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">
                                    <i class="fas fa-crown mr-1"></i>INTOCABLE
                                </span>
                            @endif
                        </td>
                        <td class="p-3">{{ $rol->descripcion ?? 'Sin descripción' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $rol->habilitado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $rol->habilitado ? 'Habilitado' : 'Deshabilitado' }}
                            </span>
                        </td>
                        <td class="p-3">{{ $rol->usuarios->count() }}</td>
                        <td class="p-3">
                            <!-- Debug temporal -->
                            @if(config('app.debug'))
                                <div class="text-xs text-gray-500 mb-1">
                                    Debug: isReadOnly={{ isset($isReadOnly) ? ($isReadOnly ? 'true' : 'false') : 'not_set' }}, 
                                    User: {{ auth()->user()->email ?? 'no_auth' }}, 
                                    Role: {{ auth()->user()->id_rol ?? 'no_role' }}
                                </div>
                            @endif
                            
                            <div class="flex space-x-2">
                                <a href="{{ route('roles.show', $rol) }}" 
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </a>
                                @if(!isset($isReadOnly) || !$isReadOnly)
                                    @if($rol->nombre === 'Administrador' || $rol->id_rol == 1)
                                        <button disabled class="bg-purple-400 text-white px-3 py-1 rounded text-sm cursor-not-allowed" title="¡No puedes tocar al admin! 😂">
                                            <i class="fas fa-crown mr-1"></i>Intocable
                                        </button>
                                        <button disabled class="bg-purple-400 text-white px-3 py-1 rounded text-sm cursor-not-allowed" title="El admin es sagrado 👑">
                                            <i class="fas fa-shield-alt mr-1"></i>Protegido
                                        </button>
                                    @else
                                        <a href="{{ route('roles.edit', $rol) }}" 
                                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </a>
                                        @if($rol->usuarios->count() == 0)
                                            <form method="POST" action="{{ route('roles.destroy', $rol) }}" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este rol?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                                    <i class="fas fa-trash mr-1"></i>Eliminar
                                                </button>
                                            </form>
                                        @else
                                            <button disabled class="bg-gray-400 text-white px-3 py-1 rounded text-sm cursor-not-allowed" title="No se puede eliminar porque tiene usuarios asignados">
                                                <i class="fas fa-lock mr-1"></i>Protegido
                                            </button>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>