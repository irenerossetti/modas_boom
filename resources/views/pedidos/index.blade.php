<x-app-layout>
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">Pedidos</h1>
      <a href="{{ route('pedidos.create') }}" class="inline-flex items-center px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
        + Nuevo pedido
      </a>
    </div>

    @if (session('success'))
      <div class="mb-4 p-3 rounded bg-green-50 text-green-700 text-sm">
        {{ session('success') }}
      </div>
    @endif

    <div class="overflow-x-auto border rounded">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 border-b text-left">
          <tr>
            <th class="py-2 px-3">#</th>
            <th class="py-2 px-3">Cliente</th>
            <th class="py-2 px-3">Fecha pedido</th>
            <th class="py-2 px-3">Estado</th>
            <th class="py-2 px-3">Total</th>
            <th class="py-2 px-3 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pedidos as $p)
            <tr class="border-b">
              <td class="py-2 px-3">{{ $p->id_pedido }}</td>
              <td class="py-2 px-3">
                @if($p->cliente)
                  {{ $p->cliente->nombre }} {{ $p->cliente->apellido }}
                @else
                  <span class="text-gray-500">—</span>
                @endif
              </td>
              <td class="py-2 px-3">
                @if($p->fecha_pedido)
                  {{ \Illuminate\Support\Carbon::parse($p->fecha_pedido)->format('Y-m-d') }}
                @else
                  <span class="text-gray-500">—</span>
                @endif
              </td>
              <td class="py-2 px-3">{{ $p->estado }}</td>
              <td class="py-2 px-3">{{ number_format((float)($p->total_pedido ?? 0), 2) }}</td>
              <td class="py-2 px-3 text-right">
                <a href="{{ route('pedidos.edit', $p->id_pedido) }}" class="text-blue-600 hover:underline">Editar</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-6 px-3 text-center text-gray-500">No hay pedidos aún.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $pedidos->links() }}
    </div>
  </div>
</x-app-layout>
