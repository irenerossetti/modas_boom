@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-boom-text-dark flex items-center">
            <i class="fas fa-chart-line mr-3 text-boom-red-title"></i>
            Análisis de Productos: Estrella y Hueso
        </h1>
        <p class="text-sm text-gray-600 mt-2">
            Identifica los productos más vendidos (Estrella ⭐) y menos vendidos (Hueso 💀) para tomar decisiones estratégicas
        </p>
    </div>

    <!-- Formulario de filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form action="{{ route('reportes.analisis-productos') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Fecha Desde -->
                <div>
                    <label for="fecha_desde" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1 text-boom-red-title"></i>
                        Fecha Desde
                    </label>
                    <input type="date" 
                           id="fecha_desde" 
                           name="fecha_desde" 
                           value="{{ request('fecha_desde') }}"
                           class="w-full border-2 border-boom-rose-dark focus:border-boom-red-title focus:ring-boom-red-title rounded-lg shadow-md px-3 py-2">
                </div>

                <!-- Fecha Hasta -->
                <div>
                    <label for="fecha_hasta" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-check mr-1 text-boom-red-title"></i>
                        Fecha Hasta
                    </label>
                    <input type="date" 
                           id="fecha_hasta" 
                           name="fecha_hasta" 
                           value="{{ request('fecha_hasta') }}"
                           class="w-full border-2 border-boom-rose-dark focus:border-boom-red-title focus:ring-boom-red-title rounded-lg shadow-md px-3 py-2">
                </div>

                <!-- Formato -->
                <div>
                    <label for="formato" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-file-export mr-1 text-boom-red-title"></i>
                        Formato de Exportación
                    </label>
                    <select id="formato" 
                            name="formato" 
                            class="w-full border-2 border-boom-rose-dark focus:border-boom-red-title focus:ring-boom-red-title rounded-lg shadow-md px-3 py-2">
                        <option value="pdf" {{ request('formato') == 'pdf' ? 'selected' : '' }}>📄 PDF</option>
                        <option value="excel" {{ request('formato') == 'excel' ? 'selected' : '' }}>📊 Excel (.xls)</option>
                        <option value="csv" {{ request('formato') == 'csv' ? 'selected' : '' }}>📋 CSV (UTF-8)</option>
                        <option value="json" {{ request('formato') == 'json' ? 'selected' : '' }}>🔧 JSON</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-boom-red-title border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-boom-red-dark focus:outline-none focus:ring-2 focus:ring-boom-red-title focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                    <i class="fas fa-download mr-2"></i>
                    Generar Reporte
                </button>

                @if(request()->hasAny(['fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('reportes.analisis-productos') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gray-500 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                        <i class="fas fa-times mr-2"></i>
                        Limpiar Filtros
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Información sobre el reporte -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Productos Estrella -->
        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center mb-4">
                <div class="bg-yellow-500 rounded-full p-3 mr-4">
                    <i class="fas fa-star text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Productos Estrella</h3>
                    <p class="text-sm text-gray-600">Los más vendidos</p>
                </div>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    <span>Identifica los productos con mayor demanda</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    <span>Analiza ingresos generados por producto</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    <span>Optimiza tu inventario basado en ventas reales</span>
                </li>
            </ul>
        </div>

        <!-- Productos Hueso -->
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg shadow-md p-6 border-l-4 border-gray-500">
            <div class="flex items-center mb-4">
                <div class="bg-gray-500 rounded-full p-3 mr-4">
                    <i class="fas fa-bone text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Productos Hueso</h3>
                    <p class="text-sm text-gray-600">Los menos vendidos</p>
                </div>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-orange-500 mr-2 mt-1"></i>
                    <span>Detecta productos con baja rotación</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-orange-500 mr-2 mt-1"></i>
                    <span>Identifica oportunidades de promoción</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-orange-500 mr-2 mt-1"></i>
                    <span>Toma decisiones sobre descontinuación</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Formatos de Exportación -->
    <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg">
        <div class="flex items-start">
            <i class="fas fa-file-download text-purple-500 text-xl mr-3 mt-1"></i>
            <div>
                <h4 class="font-bold text-purple-900 mb-2">📥 Formatos de Exportación Disponibles:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-purple-800">
                    <div>
                        <strong>📄 PDF:</strong> Reporte visual profesional con gráficos y colores
                    </div>
                    <div>
                        <strong>📊 Excel:</strong> Archivo .xls compatible con Microsoft Excel (UTF-8)
                    </div>
                    <div>
                        <strong>📋 CSV:</strong> Datos separados por punto y coma, compatible con Excel (UTF-8)
                    </div>
                    <div>
                        <strong>🔧 JSON:</strong> Formato estructurado para integración con otras herramientas
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex items-start">
            <i class="fas fa-lightbulb text-blue-500 text-xl mr-3 mt-1"></i>
            <div>
                <h4 class="font-bold text-blue-900 mb-2">💡 Tips para usar este reporte:</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Genera reportes mensuales para identificar tendencias</li>
                    <li>• Compara diferentes períodos para ver evolución</li>
                    <li>• Usa los productos estrella para planificar tu inventario</li>
                    <li>• Crea promociones para los productos hueso antes de descontinuarlos</li>
                    <li>• Exporta a Excel/CSV para análisis más profundos con tablas dinámicas</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
