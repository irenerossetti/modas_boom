{{-- resources/views/pedidos/create.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl leading-tight">
      Registrar Pedido
    </h2>
  </x-slot>

  <div class="max-w-6xl mx-auto p-6">
    @if (session('success'))
      <div class="mb-4 p-3 rounded bg-green-50 text-green-700 text-sm">
        {{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-sm">
        <ul class="list-disc ml-5">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('pedidos.store') }}">
      @csrf

      @include('pedidos._form', [
        'mode'      => 'create',
        'pedido'    => null,
        'clientes'  => $clientes,
        'productos' => $productos,
      ])
    </form>
  </div>
</x-app-layout>
