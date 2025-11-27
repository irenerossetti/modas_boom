@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-center">Inventario de Telas</h2>
                    <a href="{{ route('telas.create') }}" class="bg-gray-400 text-black px-4 py-2 rounded">Registrar Tela</a>
                </div>
                <table class="w-full text-center">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Nombre</th>
                            <th class="p-3">Stock</th>
                            <th class="p-3">Medida</th>
                            <th class="p-3">Stock Mínimo</th>
                            <th class="p-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($telas as $tela)
                            <tr>
                                <td class="p-3">{{ $tela->id }}</td>
                                <td class="p-3">{{ $tela->nombre }}</td>
                                <td class="p-3">{{ number_format($tela->stock, 2) }}</td>
                                <td class="p-3">{{ $tela->unidad }}</td>
                                <td class="p-3">{{ number_format($tela->stock_minimo, 2) }}</td>
                                <td>
                                    <a href="{{ route('telas.edit', $tela->id) }}" class="text-blue-600 hover:underline mr-2">Editar</a>
                                    <form action="{{ route('telas.consumir', $tela->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="cantidad" value="1">
                                        <button class="text-red-600" title="Consumir 1 {{ $tela->unidad }}">Consumir</button>
                                    </form>
                                    @if($tela->isLowStock())
                                        <span class="text-yellow-700 ml-2">⚠️ Stock bajo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">{{ $telas->links() }}</div>
            </div>
        </div>
    </div>
@endsection
