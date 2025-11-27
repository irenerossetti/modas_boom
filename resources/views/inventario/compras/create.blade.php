@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-4">Registrar Compra de Insumos</h2>
            <form method="POST" action="{{ route('compras.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block">Proveedor</label>
                    <select name="proveedor_id" class="form-input w-full">
                        @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block">Descripcion</label>
                    <textarea name="descripcion" class="form-input w-full"></textarea>
                </div>
                <div class="mb-3">
                    <label class="block">Tela (opcional; si la compra es para una tela, seleccionarla para ajustar el stock)</label>
                    <select name="tela_id" class="form-input w-full">
                        <option value="">-- Ninguna --</option>
                        @foreach(\App\Models\Tela::orderBy('nombre')->get() as $tela)
                            <option value="{{ $tela->id }}">{{ $tela->nombre }} ({{ $tela->unidad }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block">Cantidad (opcional si afecta stock)</label>
                    <input name="cantidad" type="number" step="0.01" class="form-input w-full" value="0">
                </div>
                <div class="mb-3">
                    <label class="block">Monto</label>
                    <input name="monto" type="number" step="0.01" class="form-input w-full" value="0">
                </div>
                <div class="mb-3">
                    <label class="block">Fecha de Compra (opcional)</label>
                    <input name="fecha_compra" type="date" class="form-input w-full">
                </div>
                <div>
                    <button class="bg-gray-400 text-black px-4 py-2 rounded">Registrar Compra</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
