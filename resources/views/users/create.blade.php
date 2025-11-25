@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-boom-text-dark mb-6">Crear Nuevo Usuario</h1>

            <div class="bg-boom-cream-100 p-6 rounded-xl shadow">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <!-- Rol -->
                    <div class="mb-4">
                        <label for="id_rol" class="block text-sm font-medium text-boom-text-dark mb-2">Rol</label>
                        <select id="id_rol" name="id_rol" class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-red-title" required>
                            <option value="">Seleccionar rol...</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_rol')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nombre -->
                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-medium text-boom-text-dark mb-2">Nombre</label>
                        <input id="nombre" name="nombre" type="text" class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-red-title" value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Teléfono -->
                    <div class="mb-4">
                        <label for="telefono" class="block text-sm font-medium text-boom-text-dark mb-2">Teléfono</label>
                        <input id="telefono" name="telefono" type="text" class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-red-title" value="{{ old('telefono') }}">
                        @error('telefono')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dirección -->
                    <div class="mb-4">
                        <label for="direccion" class="block text-sm font-medium text-boom-text-dark mb-2">Dirección</label>
                        <textarea id="direccion" name="direccion" rows="3" class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-red-title">{{ old('direccion') }}</textarea>
                        @error('direccion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-boom-text-dark mb-2">Email</label>
                        <input id="email" name="email" type="email" class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-red-title" value="{{ old('email') }}" required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-boom-text-dark mb-2">Contraseña</label>
                        <input id="password" name="password" type="password" class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-red-title" required>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-boom-text-dark mb-2">Confirmar Contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-red-title" required>
                    </div>

                    <!-- Habilitado -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="habilitado" value="1" {{ old('habilitado') ? 'checked' : '' }} class="rounded border-boom-cream-300 text-boom-red-title focus:ring-boom-red-title">
                            <span class="ml-2 text-sm text-boom-text-dark">Usuario habilitado</span>
                        </label>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('users.index') }}" class="px-4 py-2 text-boom-text-dark border border-boom-cream-400 rounded-lg hover:bg-boom-cream-200 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 bg-boom-red-report text-white rounded-lg hover:bg-boom-red-title transition-colors">
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
