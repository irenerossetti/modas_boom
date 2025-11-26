@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-boom-text-dark">Editar Rol</h2>
                            <p class="text-sm text-boom-text-medium mt-1">{{ $rol->nombre }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('roles.show', $rol) }}" 
                               class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                <i class="fas fa-eye mr-2"></i>Ver
                            </a>
                            <a href="{{ route('roles.index') }}" 
                               class="bg-white hover:bg-gray-50 text-boom-text-dark border-2 border-boom-cream-500 hover:border-boom-cream-600 font-semibold py-2 px-4 rounded-lg transition-colors duration-300 shadow-sm hover:shadow-md">
                                <i class="fas fa-arrow-left mr-2"></i>Volver
                            </a>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('roles.update', $rol) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-6">
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-boom-text-dark mb-1">
                                    Nombre del Rol *
                                </label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $rol->nombre) }}" required
                                       class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50"
                                       placeholder="Ej: Administrador, Empleado, Cliente">
                            </div>

                            <div>
                                <label for="descripcion" class="block text-sm font-medium text-boom-text-dark mb-1">
                                    Descripción
                                </label>
                                <textarea name="descripcion" id="descripcion" rows="4"
                                          class="w-full rounded-md border-boom-cream-300 shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50"
                                          placeholder="Descripción del rol y sus responsabilidades...">{{ old('descripcion', $rol->descripcion) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-boom-text-dark mb-3">Estado del Rol</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="habilitado" value="1" {{ old('habilitado', $rol->habilitado) ? 'checked' : '' }}
                                               class="rounded border-boom-cream-300 text-boom-rose-dark shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-boom-text-dark">
                                            <i class="fas fa-check text-green-600 mr-1"></i>Habilitado
                                        </span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="habilitado" value="0" {{ old('habilitado', $rol->habilitado) ? '' : 'checked' }}
                                               class="rounded border-boom-cream-300 text-boom-rose-dark shadow-sm focus:border-boom-rose-dark focus:ring focus:ring-boom-rose-light focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-boom-text-dark">
                                            <i class="fas fa-times text-red-600 mr-1"></i>Deshabilitado
                                        </span>
                                    </label>
                                </div>
                                <p class="text-xs text-boom-text-medium mt-1">Los roles deshabilitados no pueden ser asignados a nuevos usuarios</p>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('roles.show', $rol) }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300">
                                <i class="fas fa-save mr-2"></i>Actualizar Rol
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
