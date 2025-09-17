<x-app-layout>
  <x-slot name="header">
    <h2 class="text-2xl font-bold text-[#A51420]">Dashboard Administrativo</h2>
  </x-slot>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Cards de métricas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      @php
        $card = 'rounded-2xl bg-white shadow-soft p-5 border border-[#F4E3DA]';
        $head = 'text-sm text-slate-500 font-medium';
        $num  = 'mt-2 text-3xl font-bold text-[#B21724]';
        $sub  = 'text-xs text-slate-500';
      @endphp

      <div class="{{ $card }}">
        <p class="{{ $head }}">Total Usuarios</p>
        <p class="{{ $num }}">1,234</p>
        <p class="{{ $sub }}">+12% este mes</p>
      </div>

      <div class="{{ $card }}">
        <p class="{{ $head }}">Pedidos Activos</p>
        <p class="{{ $num }}">89</p>
        <p class="{{ $sub }}">+5 hoy</p>
      </div>

      <div class="{{ $card }}">
        <p class="{{ $head }}">Inventario</p>
        <p class="{{ $num }}">456</p>
        <p class="{{ $sub }}">Artículos en stock</p>
      </div>

      <div class="{{ $card }}">
        <p class="{{ $head }}">Ingresos</p>
        <p class="{{ $num }} text-brand-rose">$45,231</p>
        <p class="{{ $sub }}">+18% este mes</p>
      </div>
    </div>

    {{-- Dos columnas principales --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <section class="rounded-2xl bg-white border border-[#F4E3DA] shadow-soft">
        <header class="px-6 py-4 border-b border-[#F4E3DA] flex items-center gap-2">
          <span class="text-[#B21724]">🛒</span>
          <h3 class="font-semibold text-slate-800">Pedidos Recientes</h3>
        </header>

        <ul class="p-4 space-y-4">
          <li class="flex items-center justify-between rounded-xl border border-[#F4E3DA] p-4">
            <div>
              <p class="font-medium text-slate-800">María García</p>
              <p class="text-sm text-slate-500">Pedido #001</p>
            </div>
            <span class="inline-flex rounded-full bg-[#F6C5BD] text-[#7E1B19] text-xs px-3 py-1">En proceso</span>
          </li>

          <li class="flex items-center justify-between rounded-xl border border-[#F4E3DA] p-4">
            <div>
              <p class="font-medium text-slate-800">Juan Pérez</p>
              <p class="text-sm text-slate-500">Pedido #002</p>
            </div>
            <span class="inline-flex rounded-full bg-[#B21724] text-white text-xs px-3 py-1">Completado</span>
          </li>
        </ul>
      </section>

      <section class="rounded-2xl bg-white border border-[#F4E3DA] shadow-soft">
        <header class="px-6 py-4 border-b border-[#F4E3DA] flex items-center gap-2">
          <span class="text-[#B21724]">🔔</span>
          <h3 class="font-semibold text-slate-800">Tareas y Alertas</h3>
        </header>

        <div class="p-4 space-y-4">
          <div class="rounded-xl bg-[#FFE3E3] text-[#7E1B19] border border-[#F4B9B9] p-4">
            <p class="font-medium">Stock bajo</p>
            <p class="text-sm">Tela de algodón · Solo 5 m restantes</p>
          </div>
          <div class="rounded-xl bg-[#FFF3E3] text-[#7A4D1C] border border-[#F8DDB7] p-4">
            <p class="font-medium">Entrega programada</p>
            <p class="text-sm">3 pedidos para mañana</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
