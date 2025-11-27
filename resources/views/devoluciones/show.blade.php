@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-boom-text-dark">Devolución #{{ $devolucion->id }}</h2>

                <div class="mt-4">
                    <p><strong>Pedido:</strong> #{{ $devolucion->id_pedido }}</p>
                    <p><strong>Prenda:</strong> {{ $devolucion->prenda->nombre }}</p>
                    <p><strong>Cantidad:</strong> {{ $devolucion->cantidad }}</p>
                    <p><strong>Motivo:</strong> {{ $devolucion->motivo }}</p>
                    <p><strong>Registrado por:</strong> {{ $devolucion->registradoPor->nombre ?? 'Sistema' }}</p>
                    <p><strong>Fecha:</strong> {{ $devolucion->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div class="mt-6">
                    <a href="{{ route('devoluciones.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">Volver a Devoluciones</a>
                </div>
            </div>
        </div>
    </div>
@endsection
