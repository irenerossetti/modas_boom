@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-4">Auditoría de Movimientos de Inventario (Última Semana)</h2>

            <h3 class="font-bold mb-2">Compras</h3>
            <table class="w-full text-center mb-4">
                <thead>
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Proveedor</th>
                        <th class="p-3">Descripcion</th>
                        <th class="p-3">Monto</th>
                        <th class="p-3">Fecha Compra</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compras as $compra)
                        <tr>
                            <td class="p-3">{{ $compra->id }}</td>
                            <td class="p-3">{{ $compra->proveedor->nombre }}</td>
                            <td class="p-3">{{ $compra->descripcion }}</td>
                            <td class="p-3">{{ number_format($compra->monto, 2) }}</td>
                            <td class="p-3">{{ optional($compra->fecha_compra)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td class="p-3" colspan="5">No hay compras en la última semana.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <h3 class="font-bold mb-2">Bitácora (INVENTARIO)</h3>
            <table class="w-full text-center">
                <thead>
                    <tr>
                        <th class="p-3">Fecha</th>
                        <th class="p-3">Usuario</th>
                        <th class="p-3">Acción</th>
                        <th class="p-3">Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bitacoras as $registro)
                        <tr>
                            <td class="p-3">{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3">{{ $registro->nombre_usuario ?? 'Sistema' }}</td>
                            <td class="p-3">{{ $registro->accion }}</td>
                            <td class="p-3">{{ $registro->descripcion }}</td>
                        </tr>
                    @empty
                        <tr><td class="p-3" colspan="4">No hay movimientos en la bitácora en la última semana.</td></tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection
