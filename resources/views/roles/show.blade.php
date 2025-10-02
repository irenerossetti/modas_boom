<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles del Rol: ') . $rol->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Información del Rol</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="mb-2"><strong>ID:</strong> {{ $rol->id_rol }}</p>
                                <p class="mb-2"><strong>Nombre:</strong> {{ $rol->nombre }}</p>
                                <p class="mb-2"><strong>Descripción:</strong> {{ $rol->descripcion ?? 'Sin descripción' }}</p>
                                <p class="mb-2"><strong>Estado:</strong>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $rol->habilitado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $rol->habilitado ? 'Habilitado' : 'Deshabilitado' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Usuarios con este Rol</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="mb-2"><strong>Total de usuarios:</strong> {{ $rol->usuarios->count() }}</p>
                                @if($rol->usuarios->count() > 0)
                                    <div class="mt-3">
                                        <p class="text-sm text-gray-600 mb-2">Usuarios asignados:</p>
                                        <ul class="list-disc list-inside text-sm">
                                            @foreach($rol->usuarios as $usuario)
                                                <li>{{ $usuario->nombre }} ({{ $usuario->email }})</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 mt-2">No hay usuarios asignados a este rol.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('roles.edit', $rol) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Editar Rol
                        </a>
                        <a href="{{ route('roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Volver a la Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>