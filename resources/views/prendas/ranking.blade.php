@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-boom-text-dark">Ranking de Productos Más Vendidos</h2>
                            <p class="text-sm text-boom-text-medium mt-1">Top 50 prenda(s) por unidades vendidas</p>
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-sm text-boom-text-medium">
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">Prenda</th>
                                <th class="px-4 py-2">Categoría</th>
                                <th class="px-4 py-2">Unidades Vendidas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ranking as $index => $item)
                                <tr class="border-t">
                                    <td class="px-4 py-3">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">{{ $item->nombre }}</td>
                                    <td class="px-4 py-3">{{ $item->categoria }}</td>
                                    <td class="px-4 py-3">{{ $item->total_vendidos }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
