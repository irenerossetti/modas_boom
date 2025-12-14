@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-boom-text-dark">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Mis Pagos
                    </h2>
                    
                    @if($cliente)
                    <div class="text-right">
                         <span class="text-sm text-gray-600 block">Total Pagado Histórico:</span>
                         <span class="text-xl font-bold text-green-600">Bs. {{ number_format($totalPagado, 2) }}</span>
                    </div>
                    @endif
                </div>

                @if(!$cliente || $pagos->isEmpty())
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <i class="fas fa-receipt text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No tienes pagos registrados aún.</p>
                        <a href="{{ route('pedidos.mis-pedidos') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium">
                            Ver mis pedidos
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pago</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pagos as $pago)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            #{{ $pago->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <a href="{{ route('pedidos.show', $pago->id_pedido) }}" class="text-blue-600 hover:text-blue-900 hover:underline">
                                                Pedido #{{ $pago->id_pedido }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            Bs. {{ number_format($pago->monto, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $pago->fecha_pago->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                            {{ $pago->metodo }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($pago->anulado)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Anulado
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Procesado
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if(!$pago->anulado)
                                                <a href="{{ route('pagos.recibo', $pago->id) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                    <i class="fas fa-download mr-1.5"></i> Recibo
                                                </a>
                                            @else
                                                <span class="text-gray-400 italic text-xs">No disponible</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
