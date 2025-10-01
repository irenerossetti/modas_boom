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
    <div>
        <label for="nombre" class="block font-medium text-sm text-gray-700">Nombre</label>
        <input id="nombre" name="nombre" type="text" class="form-input mt-1 block w-full rounded-md shadow-sm" value="{{ old('nombre', $cliente->nombre ?? '') }}" required>
    </div>
    <div>
        <label for="apellido" class="block font-medium text-sm text-gray-700">Apellido</label>
        <input id="apellido" name="apellido" type="text" class="form-input mt-1 block w-full rounded-md shadow-sm" value="{{ old('apellido', $cliente->apellido ?? '') }}" required>
    </div>
    <div>
        <label for="ci_nit" class="block font-medium text-sm text-gray-700">CI o NIT</label>
        <input id="ci_nit" name="ci_nit" type="text" class="form-input mt-1 block w-full rounded-md shadow-sm" value="{{ old('ci_nit', $cliente->ci_nit ?? '') }}" required>
    </div>
    <div>
        <label for="telefono" class="block font-medium text-sm text-gray-700">Teléfono</label>
        <input id="telefono" name="telefono" type="text" class="form-input mt-1 block w-full rounded-md shadow-sm" value="{{ old('telefono', $cliente->telefono ?? '') }}">
    </div>
    <div class="md:col-span-2">
        <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
        <input id="email" name="email" type="email" class="form-input mt-1 block w-full rounded-md shadow-sm" value="{{ old('email', $cliente->email ?? '') }}">
    </div>
    <div class="md:col-span-2">
        <label for="direccion" class="block font-medium text-sm text-gray-700">Dirección</label>
        <textarea id="direccion" name="direccion" rows="3" class="form-textarea mt-1 block w-full rounded-md shadow-sm">{{ old('direccion', $cliente->direccion ?? '') }}</textarea>
    </div>
</div>

<div class="flex items-center justify-end mt-4">
    <a href="{{ route('clientes.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Guardar
    </button>
</div>