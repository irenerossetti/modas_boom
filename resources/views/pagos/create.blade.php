@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-boom-text-dark mb-4">Registrar Pago - Pedido #{{ $pedido->id_pedido }}</h2>
                <form action="{{ route('pedidos.pagos.store', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Monto (Bs.)</label>
                        <input type="number" step="0.01" name="monto" required class="w-full p-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Método</label>
                        <input type="text" name="metodo" class="w-full p-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Referencia</label>
                        <input type="text" name="referencia" class="w-full p-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Fecha de Pago</label>
                        <input type="date" name="fecha_pago" class="w-full p-2 border rounded">
                    </div>
                    <div class="flex gap-2">
                        <button class="bg-boom-rose-dark text-black px-4 py-2 rounded">Registrar Pago</button>
                        <a class="bg-gray-500 text-white px-4 py-2 rounded" href="{{ route('pedidos.show', $pedido->id_pedido) }}">Volver</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
