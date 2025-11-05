@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-boom-text-dark">Gestión de Usuarios</h1>
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-boom-red-report text-white rounded-lg hover:bg-boom-red-title transition-colors">
                Nuevo Usuario
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
            <table class="w-full text-left">
                <thead class="text-boom-text-medium">
                    <tr>
                        <th class="p-3">Num</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Rol</th>
                        <th class="p-3">Estado</th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-boom-cream-200">
                    @foreach ($users as $user)
                    <tr class="text-boom-text-dark">
                        <td class="p-3">{{ $loop->iteration }}</td>
                        <td class="p-3 font-bold">{{ $user->nombre }}</td>
                        <td class="p-3">{{ $user->email }}</td>
                        <td class="p-3">{{ $user->rol->nombre ?? 'Sin rol' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $user->habilitado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->habilitado ? 'Habilitado' : 'Deshabilitado' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <a href="{{ route('users.edit', $user->id_usuario) }}" class="text-blue-500 hover:underline mr-4">Editar</a>
                            <form method="POST" action="{{ route('users.destroy', $user->id_usuario) }}" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este usuario?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
