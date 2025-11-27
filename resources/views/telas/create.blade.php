@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4 ">Registrar Tela</h2>
                <form action="{{ route('telas.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="block">Nombre</label>
                        <input name="nombre" class="form-input w-full">
                    </div>
                    <div class="mb-3">
                        <label class="block">Descripcion</label>
                        <textarea name="descripcion" class="form-input w-full"></textarea>
                    </div>
                    <div class="mb-3 grid grid-cols-3 gap-3">
                        <div>
                            <label class="block">Stock</label>
                            <input name="stock" type="number" step="0.01" class="form-input w-full" value="0">
                        </div>
                        <div>
                            <label class="block">Unidad</label>
                            <input name="unidad" class="form-input w-full" value="m">
                        </div>
                        <div>
                            <label class="block">Stock mínimo</label>
                            <input name="stock_minimo" type="number" step="0.01" class="form-input w-full" value="0">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button class="bg-gray-400 text-black px-4 py-2 rounded">Registrar Tela</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
