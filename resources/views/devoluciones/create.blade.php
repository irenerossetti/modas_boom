@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-boom-text-dark mb-4">Registrar Devolución - Pedido #{{ $pedido->id_pedido }}</h2>

                    <form action="{{ route('pedidos.devoluciones.store', $pedido->id_pedido) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-boom-text-dark mb-1">Prenda</label>
                            <select name="prenda_id" class="w-full rounded-md border-boom-cream-300 p-2">
                                <option value="">Seleccionar prenda</option>
                                @foreach($pedido->prendas as $prenda)
                                    <option value="{{ $prenda->id }}" data-cantidad="{{ $prenda->pivot->cantidad }}" @if(request()->get('prenda_id') == $prenda->id) selected @endif>{{ $prenda->nombre }} (Cantidad: {{ $prenda->pivot->cantidad }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-boom-text-dark mb-1">Cantidad</label>
                            <input type="number" name="cantidad" id="cantidad_devolucion" min="1" value="1" class="w-full rounded-md border-boom-cream-300 p-2">
                            <p id="max_cantidad_note" class="text-xs text-gray-500 mt-2">Cantidad máxima permitida: <span id="max_cantidad">-</span></p>
                        </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const select = document.querySelector('select[name="prenda_id"]');
                            const maxNote = document.getElementById('max_cantidad');
                            const cantidadInput = document.getElementById('cantidad_devolucion');

                            function updateMax() {
                                const option = select.options[select.selectedIndex];
                                const max = option ? option.getAttribute('data-cantidad') : null;
                                if (max) {
                                    maxNote.textContent = max + ' unidades';
                                    cantidadInput.max = max;
                                } else {
                                    maxNote.textContent = '-';
                                    cantidadInput.removeAttribute('max');
                                }
                            }

                            select.addEventListener('change', updateMax);
                            updateMax();
                        });
                    </script>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-boom-text-dark mb-1">Motivo</label>
                            <textarea name="motivo" rows="3" class="w-full rounded-md border-boom-cream-300 p-2"></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button class="bg-boom-rose-dark hover:bg-boom-rose-light text-white px-4 py-2 rounded-md">Registrar Devolución</button>
                            <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">Volver</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
