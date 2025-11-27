@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-4">Editar Proveedor</h2>
            <form method="POST" action="{{ route('proveedores.update', $proveedor->id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="block">Nombre</label>
                    <input name="nombre" class="form-input w-full" value="{{ $proveedor->nombre }}" />
                </div>
                <div class="mb-3">
                    <label class="block">Contacto</label>
                    <input name="contacto" class="form-input w-full" value="{{ $proveedor->contacto }}" />
                </div>
                <div class="mb-3">
                    <label class="block">Teléfono</label>
                    <input name="telefono" class="form-input w-full" value="{{ $proveedor->telefono }}" />
                </div>
                <div class="mb-3">
                    <label class="block">Email</label>
                    <input name="email" class="form-input w-full" value="{{ $proveedor->email }}" />
                </div>
                <div>
                    <button class="bg-gray-400 text-black px-4 py-2 rounded">Actualizar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
