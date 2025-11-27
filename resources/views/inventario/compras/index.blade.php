@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-center">Compras de Insumos</h2>
                <a href="{{ route('compras.create') }}" class="bg-gray-400 text-black px-4 py-2 rounded">Registrar Compra</a>
            </div>
            <table class="w-full text-center">
                <thead>
                    <tr>
                        <th class="p-3">ID</th>
                            <th class="p-3">Proveedor</th>
                            <th class="p-3">Tela</th>
                            <th class="p-3">Cantidad</th>
                        <th class="p-3">Descripcion</th>
                        <th class="p-3">Monto</th>
                        <th class="p-3">Fecha Compra</th>
                        <th class="p-3">Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compras as $compra)
                        <tr>
                            <td class="p-3">{{ $compra->id }}</td>
                            <td class="p-3">{{ $compra->proveedor->nombre }}</td>
                            <td class="p-3">{{ $compra->descripcion }}</td>
                            <td class="p-3">{{ $compra->tela->nombre ?? '-' }}</td>
                            <td class="p-3">{{ number_format($compra->cantidad, 2) }} {{ $compra->tela->unidad ?? '' }}</td>
                            <td class="p-3">{{ number_format($compra->monto, 2) }}</td>
                            <td class="p-3">{{ optional($compra->fecha_compra)->format('d/m/Y') }}</td>
                            <td class="p-3">{{ $compra->registradoPor->nombre ?? 'Sistema' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $compras->links() }}</div>
        </div>
    </div>
</div>
@endsection
