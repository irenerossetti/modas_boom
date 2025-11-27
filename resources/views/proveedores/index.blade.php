@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-center">Proveedores</h2>
                <a href="{{ route('proveedores.create') }}" class="bg-gray-400 text-black px-4 py-2 rounded">Registrar Proveedor</a>
            </div>
            <table class="w-full text-center">
                <thead>
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Contacto</th>
                        <th class="p-3">Teléfono</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proveedores as $proveedor)
                        <tr>
                            <td class="p-3">{{ $proveedor->id }}</td>
                            <td class="p-3">{{ $proveedor->nombre }}</td>
                            <td class="p-3">{{ $proveedor->contacto }}</td>
                            <td class="p-3">{{ $proveedor->telefono }}</td>
                            <td class="p-3">{{ $proveedor->email }}</td>
                            <td class="p-3">
                                <a href="{{ route('proveedores.edit', $proveedor->id) }}" class="text-blue-600 hover:underline mr-2">Editar</a>
                                <form method="POST" action="{{ route('proveedores.destroy', $proveedor->id) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $proveedores->links() }}</div>
        </div>
    </div>
</div>
@endsection
