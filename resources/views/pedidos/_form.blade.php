{{-- resources/views/pedidos/_form.blade.php --}}

@php
  use Illuminate\Support\Js;

  // Old + pedido
  $old = [
    'id_cliente'     => old('id_cliente', isset($pedido) ? $pedido->id_cliente : ''),
    'fecha_pedido'   => old('fecha_pedido', isset($pedido) ? optional($pedido->fecha_pedido)->format('Y-m-d') : now()->format('Y-m-d')),
    'fecha_entrega'  => old('fecha_entrega', isset($pedido) ? optional($pedido->fecha_entrega)->format('Y-m-d') : ''),
    'metodo_pago'    => old('metodo_pago', isset($pedido) ? $pedido->metodo_pago : ''),
    'observaciones'  => old('observaciones', isset($pedido) ? $pedido->observaciones : ''),
  ];

  // Items iniciales
  $initialItems = collect(
    old('items', isset($pedido)
      ? $pedido->detalles->map(function ($d) {
          return [
            'id_producto'     => $d->id_producto,
            'cantidad'        => (float) $d->cantidad,
            'precio_unitario' => (float) $d->precio_unitario,
          ];
        })->values()->all()
      : [])
  );

  if ($initialItems->isEmpty()) {
    $initialItems = collect([['id_producto' => '', 'cantidad' => 1, 'precio_unitario' => 0]]);
  }

  // Productos para Alpine 
  $alpineProducts = $productos->map(function ($p) {
    return [
      'id'     => $p->id_producto,
      'nombre' => $p->nombre,
      'precio' => (float) ($p->precio_unitario ?? 0),
    ];
  })->values();
@endphp

{{-- JS de Alpine (push al stack) --}}
@include('pedidos._form_js')

<div
  x-data="pedidoForm({
    products: @js($alpineProducts->values()),
    initialItems: @js($initialItems->values()),
    initialCliente: @js($old['id_cliente']),
  })"
  x-init="init()"
  class="space-y-6"
>
  {{-- Datos del pedido --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div>
      <x-input-label for="id_cliente" value="Cliente" />
      <select id="id_cliente" name="id_cliente" x-model="id_cliente"
              class="mt-1 block w-full rounded border-gray-300">
        <option value="">-- Seleccione --</option>
        @foreach($clientes as $c)
          <option value="{{ $c->id }}">{{ $c->nombre }} {{ $c->apellido }}</option>
        @endforeach
      </select>
      <x-input-error :messages="$errors->get('id_cliente')" class="mt-1" />
    </div>

    <div>
      <x-input-label for="fecha_pedido" value="Fecha del pedido" />
      <input id="fecha_pedido" name="fecha_pedido" type="date"
             class="mt-1 block w-full rounded border-gray-300"
             value="{{ $old['fecha_pedido'] }}" required />
      <x-input-error :messages="$errors->get('fecha_pedido')" class="mt-1" />
    </div>

    <div>
      <x-input-label for="fecha_entrega" value="Fecha de entrega (opcional)" />
      <input id="fecha_entrega" name="fecha_entrega" type="date"
             class="mt-1 block w-full rounded border-gray-300"
             value="{{ $old['fecha_entrega'] }}" />
      <x-input-error :messages="$errors->get('fecha_entrega')" class="mt-1" />
    </div>

    <div>
      <x-input-label for="metodo_pago" value="Método de pago" />
      <input id="metodo_pago" name="metodo_pago" type="text"
             class="mt-1 block w-full rounded border-gray-300"
             value="{{ $old['metodo_pago'] }}" />
      <x-input-error :messages="$errors->get('metodo_pago')" class="mt-1" />
    </div>

    <div class="md:col-span-2">
      <x-input-label for="observaciones" value="Observaciones" />
      <textarea id="observaciones" name="observaciones" rows="3"
                class="mt-1 block w-full rounded border-gray-300">{{ $old['observaciones'] }}</textarea>
      <x-input-error :messages="$errors->get('observaciones')" class="mt-1" />
    </div>
  </div>

  {{-- Ítems --}}
   <div class="flex items-center justify-between mb-3">
    <h3 class="font-semibold">Ítems</h3>
    <button type="button"
            class="inline-flex items-center px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700"
            @click.prevent="addItem()">
      + Agregar ítem
    </button>
  </div>
    <x-input-error :messages="$errors->get('items')" class="mb-3" />

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="border-b bg-gray-50">
          <tr class="text-left">
            <th class="py-2 px-2 w-72">Producto</th>
            <th class="py-2 px-2 w-28">Cantidad</th>
            <th class="py-2 px-2 w-36">Precio unit.</th>
            <th class="py-2 px-2 w-36">Subtotal</th>
            <th class="py-2 px-2 w-16"></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="(it, idx) in items" :key="idx">
            <tr class="border-b">
              <td class="py-2 px-2">
                <select class="w-full rounded border-gray-300"
                        :name="'items[' + idx + '][id_producto]'"
                        x-model="it.id_producto"
                        @change="onProductChange(idx)">
                <option value="">-- Seleccione --</option>

                {{-- Si el producto del item no está en products, muestra opción ad-hoc --}}
                <template x-if="it.id_producto && !products.some(p => String(p.id) === String(it.id_producto))">
                    <option :value="String(it.id_producto)"
                            x-text="'Producto #' + it.id_producto + ' (inactivo)'"></option>
                </template>

                {{-- Opciones normales (incluye ghosts) --}}
                <template x-for="p in products" :key="p.id">
                    <option :value="String(p.id)"
                            x-text="p.nombre + (p._ghost ? ' (inactivo)' : '')"></option>
                </template>
                </select>


              </td>

              <td class="py-2 px-2">
                <input type="number" step="0.01" min="0.01" inputmode="decimal" autocomplete="off"
                       class="w-24 rounded border-gray-300"
                       :name="'items[' + idx + '][cantidad]'"
                       x-model.number="it.cantidad" />
              </td>

              <td class="py-2 px-2">
                <input type="number" step="0.01" min="0" inputmode="decimal" autocomplete="off"
                       class="w-32 rounded border-gray-300"
                       :name="'items[' + idx + '][precio_unitario]'"
                       x-model.number="it.precio_unitario" />
              </td>

              <td class="py-2 px-2">
                <span x-text="formatMoney(lineSubtotal(idx))"></span>
              </td>

              <td class="py-2 px-2 text-right">
                <button type="button" class="px-2 py-1 rounded border hover:bg-gray-50"
                        @click="removeItem(idx)">✕</button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="mt-4 text-right">
      <span class="font-semibold">Total: </span>
      <span class="text-lg font-semibold" x-text="formatMoney(total())"></span>
    </div>

    <input type="hidden" name="total_pedido" :value="total().toFixed(2)">
  </div>

  {{-- Acciones --}}
  <div class="flex items-center gap-3">
    <x-primary-button type="submit">
      {{ ($mode ?? 'create') === 'edit' ? 'Actualizar Pedido' : 'Guardar Pedido' }}
    </x-primary-button>

    <a href="{{ route('pedidos.index') }}">
      <x-secondary-button type="button">Cancelar</x-secondary-button>
    </a>
  </div>
</div>
