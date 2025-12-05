@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-boom-text-dark">
            <i class="fas fa-chart-bar mr-2"></i>
            Reporte de Producción - Pago a Destajo
        </h1>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Filtros de Búsqueda</h3>

                    <form method="GET" action="{{ route('reportes.produccion.rendimiento') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Operario -->
                            <div>
                                <label for="operario_id" class="block text-sm font-medium text-gray-700">
                                    Operario (Opcional)
                                </label>
                                <select name="operario_id" id="operario_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos los operarios</option>
                                    @foreach($operarios as $operario)
                                        <option value="{{ $operario->id_usuario }}">
                                            {{ $operario->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Fecha Desde -->
                            <div>
                                <label for="fecha_desde" class="block text-sm font-medium text-gray-700">
                                    Fecha Desde
                                </label>
                                <input type="date" name="fecha_desde" id="fecha_desde" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Fecha Hasta -->
                            <div>
                                <label for="fecha_hasta" class="block text-sm font-medium text-gray-700">
                                    Fecha Hasta
                                </label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                🔍 Generar Reporte
                            </button>

                            <a href="{{ route('reportes.produccion.index') }}" 
                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                🔄 Limpiar Filtros
                            </a>
                        </div>
                    </form>

                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2">ℹ️ Información</h4>
                        <p class="text-sm text-blue-800">
                            Este reporte muestra el rendimiento de los operarios en la producción, 
                            incluyendo la cantidad de prendas procesadas y el total acumulado a pagar por trabajo a destajo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
