@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-boom-text-dark">{{ $rol->nombre }}</h2>
                            <p class="text-sm text-boom-text-medium mt-1">Detalles del rol</p>
                        </div>
                        <div class="flex space-x-2">
                            @if(!isset($isReadOnly) || !$isReadOnly)
                                @if($rol->nombre === 'Administrador' || $rol->id_rol == 1)
                                    <button disabled class="bg-purple-400 text-white font-semibold py-2 px-4 rounded-lg cursor-not-allowed" title="¡No puedes tocar al admin! 😂">
                                        <i class="fas fa-crown mr-2"></i>Intocable
                                    </button>
                                @else
                                    <a href="{{ route('roles.edit', $rol) }}" 
                                       class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                        <i class="fas fa-edit mr-2"></i>Editar
                                    </a>
                                @endif
                            @endif
                            <a href="{{ route('roles.index') }}" 
                               class="bg-white hover:bg-gray-50 text-boom-text-dark border-2 border-boom-cream-500 hover:border-boom-cream-600 font-semibold py-2 px-4 rounded-lg transition-colors duration-300 shadow-sm hover:shadow-md">
                                <i class="fas fa-arrow-left mr-2"></i>Volver
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Información del Rol -->
                        <div class="bg-boom-cream-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-info-circle mr-2"></i>Información del Rol
                            </h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-boom-text-dark">ID:</span>
                                    <span class="text-boom-text-medium">{{ $rol->id_rol }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-boom-text-dark">Nombre:</span>
                                    <span class="text-boom-text-medium font-semibold">{{ $rol->nombre }}</span>
                                </div>
                                <div class="flex justify-between items-start">
                                    <span class="font-medium text-boom-text-dark">Descripción:</span>
                                    <span class="text-boom-text-medium text-right max-w-xs">
                                        {{ $rol->descripcion ?? 'Sin descripción' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-boom-text-dark">Estado:</span>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $rol->habilitado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas {{ $rol->habilitado ? 'fa-check' : 'fa-times' }} mr-1"></i>
                                        {{ $rol->habilitado ? 'Habilitado' : 'Deshabilitado' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Usuarios con este Rol -->
                        <div class="bg-boom-cream-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-users mr-2"></i>Usuarios Asignados
                            </h3>
                            
                            <div class="text-center mb-4">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-boom-rose-dark rounded-full text-white text-2xl font-bold">
                                    {{ $rol->usuarios->count() }}
                                </div>
                                <p class="text-sm text-boom-text-medium mt-2">Total de usuarios</p>
                            </div>

                            @if($rol->usuarios->count() > 0)
                                <div class="space-y-2 max-h-64 overflow-y-auto">
                                    @foreach($rol->usuarios as $usuario)
                                        <div class="flex items-center p-3 bg-white rounded-lg border border-boom-cream-300">
                                            <div class="w-8 h-8 bg-boom-rose-dark rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                                                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-semibold text-boom-text-dark">{{ $usuario->nombre }}</p>
                                                <p class="text-sm text-boom-text-medium">{{ $usuario->email }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-user-slash text-4xl text-boom-text-light mb-3"></i>
                                    <p class="text-boom-text-medium">No hay usuarios asignados a este rol</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="mt-8 flex justify-between items-center pt-6 border-t border-boom-cream-300">
                        @if(!isset($isReadOnly) || !$isReadOnly)
                            @if($rol->nombre === 'Administrador' || $rol->id_rol == 1)
                                <div class="bg-purple-50 p-6 rounded-lg border-2 border-purple-200">
                                    <div class="text-center">
                                        <i class="fas fa-crown text-4xl text-purple-600 mb-3"></i>
                                        <h3 class="text-lg font-bold text-purple-800 mb-2">¡Rol Sagrado Detectado! 👑</h3>
                                        <p class="text-purple-700 mb-4">
                                            😂 ¡Jajaja no puedes tocar al admin! Este rol está protegido contra modificaciones.
                                        </p>
                                        <div class="flex justify-center space-x-4">
                                            <button disabled class="bg-purple-400 text-white font-semibold py-2 px-6 rounded-lg cursor-not-allowed">
                                                <i class="fas fa-shield-alt mr-2"></i>Edición Bloqueada
                                            </button>
                                            <button disabled class="bg-purple-400 text-white font-semibold py-2 px-6 rounded-lg cursor-not-allowed">
                                                <i class="fas fa-ban mr-2"></i>Eliminación Imposible
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex space-x-4">
                                    <a href="{{ route('roles.edit', $rol) }}" 
                                       class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                        <i class="fas fa-edit mr-2"></i>Editar Rol
                                    </a>
                                    
                                    @if($rol->usuarios->count() == 0)
                                        <form action="{{ route('roles.destroy', $rol) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirm('¿Estás seguro de eliminar este rol?\n\nEsta acción no se puede deshacer.')"
                                                    class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                                <i class="fas fa-trash mr-2"></i>Eliminar Rol
                                            </button>
                                        </form>
                                    @else
                                        <button disabled 
                                                class="bg-gray-400 text-white font-semibold py-2 px-6 rounded-lg cursor-not-allowed"
                                                title="No se puede eliminar porque tiene usuarios asignados">
                                            <i class="fas fa-trash mr-2"></i>No se puede eliminar
                                        </button>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-blue-800 text-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Estás viendo en modo solo lectura. Solo los administradores pueden editar roles.
                                </p>
                            </div>
                        @endif

                        <a href="{{ route('roles.index') }}" 
                           class="bg-white hover:bg-gray-50 text-boom-text-dark border-2 border-boom-cream-500 hover:border-boom-cream-600 font-semibold py-2 px-6 rounded-lg transition-colors duration-300 shadow-sm hover:shadow-md">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
