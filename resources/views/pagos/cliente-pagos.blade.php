@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-boom-text-dark mb-4">Pagos del Cliente: {{ $cliente->nombre }} {{ $cliente->apellido }}</h2>
                <div class="mb-4 flex items-center gap-6">
                    <p>Total pagado: <strong>Bs. {{ number_format($totalPagado, 2) }}</strong></p>
                    <p>Deuda actual: <strong>Bs. {{ number_format($cliente->deudaActual(), 2) }}</strong></p>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="text-left">
                            <th>ID</th>
                            <th>Pedido</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagos as $p)
                            <tr>
                                <td>{{ $p->id }}</td>
                                <td><a href="{{ route('pedidos.show', $p->id_pedido) }}">#{{ $p->id_pedido }}</a></td>
                                <td>Bs. {{ number_format($p->monto, 2) }}</td>
                                <td>{{ $p->fecha_pago->format('d/m/Y H:i') }}</td>
                                <td>{{ $p->anulado ? 'ANULADO' : 'Registrado' }}</td>
                                <td>
                                    <a href="{{ route('pagos.recibo', $p->id) }}" class="text-blue-600 hover:underline mr-2">Recibo</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
