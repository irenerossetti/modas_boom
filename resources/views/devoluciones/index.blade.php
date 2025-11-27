@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-boom-text-dark">Devoluciones de Prendas</h2>
                            <p class="text-sm text-boom-text-medium mt-1">Historial de devoluciones registradas</p>
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-sm text-boom-text-medium">
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">Pedido</th>
                                <th class="px-4 py-2">Prenda</th>
                                <th class="px-4 py-2">Cantidad</th>
                                <th class="px-4 py-2">Motivo</th>
                                <th class="px-4 py-2">Registrado por</th>
                                <th class="px-4 py-2">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($devoluciones as $devolucion)
                                <tr class="border-t text-sm">
                                    <td class="px-4 py-3">{{ $devolucion->id }}</td>
                                    <td class="px-4 py-3"><a href="{{ route('pedidos.show', $devolucion->id_pedido) }}" class="text-blue-600 hover:underline">#{{ $devolucion->id_pedido }}</a></td>
                                    <td class="px-4 py-3">{{ $devolucion->prenda->nombre }}</td>
                                    <td class="px-4 py-3">{{ $devolucion->cantidad }}</td>
                                    <td class="px-4 py-3">{{ $devolucion->motivo }}</td>
                                    <td class="px-4 py-3">{{ $devolucion->registradoPor->nombre ?? 'Sistema' }}</td>
                                    <td class="px-4 py-3">{{ $devolucion->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $devoluciones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
