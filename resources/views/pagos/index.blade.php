@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Administración de Pagos</h2>
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagos as $pago)
                            <tr>
                                <td>{{ $pago->id }}</td>
                                <td><a href="{{ route('pedidos.show', $pago->id_pedido) }}">#{{ $pago->id_pedido }}</a></td>
                                <td>{{ $pago->cliente->nombre ?? 'N/A' }}</td>
                                <td>Bs. {{ number_format($pago->monto, 2) }}</td>
                                <td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('pagos.recibo', $pago->id) }}" class="text-blue-600 hover:underline mr-2">Recibo</a>
                                    @if(!$pago->anulado)
                                        <form action="{{ route('pagos.anular', $pago->id) }}" method="POST" class="inline" onsubmit="return confirmAndSetMotivo(this)">
                                            @csrf
                                            <input type="hidden" name="motivo" value="Anulado por admin">
                                            <button class="text-red-600">Reembolsar / Anular</button>
                                        </form>
                                    @else
                                        <span class="text-gray-600">Anulado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-6">{{ $pagos->links() }}</div>
            </div>
        </div>
    </div>
    <script>
        function confirmAndSetMotivo(form) {
            const motivoInput = form.querySelector('input[name="motivo"]');
            var motivo = prompt('Ingrese motivo de anulación:', motivoInput ? motivoInput.value : 'Anulado por admin');
            if (motivo === null) return false; // usuario canceló prompt
            motivo = motivo.trim();
            if (!motivo) { alert('Debe ingresar un motivo para la anulación.'); return false; }
            if (motivoInput) motivoInput.value = motivo;
            return confirm('¿Confirmar anulación del pago?');
        }
    </script>
            </div>
        </div>
    </div>
@endsection
