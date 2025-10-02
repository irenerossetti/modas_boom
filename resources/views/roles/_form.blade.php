@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="nombre" class="block font-medium text-sm text-gray-700">Nombre del Rol</label>
        <input id="nombre" name="nombre" type="text" class="form-input mt-1 block w-full rounded-md shadow-sm" value="{{ old('nombre', $rol->nombre ?? '') }}" required>
    </div>

    <div class="md:col-span-2">
        <label for="descripcion" class="block font-medium text-sm text-gray-700">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3" class="form-textarea mt-1 block w-full rounded-md shadow-sm">{{ old('descripcion', $rol->descripcion ?? '') }}</textarea>
    </div>

    <div>
        <label class="block font-medium text-sm text-gray-700">Estado</label>
        <div class="mt-2">
            <label class="inline-flex items-center">
                <input type="radio" name="habilitado" value="1" {{ old('habilitado', $rol->habilitado ?? true) ? 'checked' : '' }} class="form-radio">
                <span class="ml-2">Habilitado</span>
            </label>
            <label class="inline-flex items-center ml-6">
                <input type="radio" name="habilitado" value="0" {{ old('habilitado', $rol->habilitado ?? true) ? '' : 'checked' }} class="form-radio">
                <span class="ml-2">Deshabilitado</span>
            </label>
        </div>
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('roles.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ isset($rol) ? 'Actualizar' : 'Crear' }} Rol
    </button>
</div>