@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-boom-text-dark mb-4">Pagos del Cliente: {{ $cliente->nombre }} {{ $cliente->apellido }}</h2>
                <form action="{{ route('clientes.recibo-consolidado', $cliente->id) }}" method="GET" class="mb-4 flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
                    <div class="flex items-center gap-2">
                        <div>
                            <label for="fecha_inicio" class="block text-xs font-medium text-gray-700">Desde</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="fecha_fin" class="block text-xs font-medium text-gray-700">Hasta</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div>
                            <p class="text-sm">Total pagado: <strong>Bs. {{ number_format($totalPagado, 2) }}</strong></p>
                            <p class="text-sm">Deuda actual: <strong>Bs. {{ number_format($cliente->deudaActual(), 2) }}</strong></p>
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded sm:ml-auto text-center flex items-center">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>Recibo Consolidado
                        </button>
                    </div>
                </form>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-max">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="pb-2">ID</th>
                                <th class="pb-2">Pedido</th>
                                <th class="pb-2">Monto</th>
                                <th class="pb-2">Fecha</th>
                                <th class="pb-2">Estado</th>
                                <th class="pb-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($pagos as $p)
                                <tr>
                                    <td class="py-3">{{ $p->id }}</td>
                                    <td class="py-3"><a href="{{ route('pedidos.show', $p->id_pedido) }}" class="text-blue-600 hover:underline">#{{ $p->id_pedido }}</a></td>
                                    <td class="py-3">Bs. {{ number_format($p->monto, 2) }}</td>
                                    <td class="py-3">{{ $p->fecha_pago->format('d/m/Y H:i') }}</td>
                                    <td class="py-3">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $p->anulado ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $p->anulado ? 'ANULADO' : 'Registrado' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right">
                                        <a href="{{ route('pagos.recibo', $p->id) }}" class="text-blue-600 hover:underline mr-2">Recibo</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
