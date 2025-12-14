@extends('layouts.app')

@section('content')
    <div class="py-4 lg:py-12">
        <div class="max-w-4xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl rounded-lg">
                <div class="p-3 sm:p-6 bg-white">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-boom-text-dark">
                                <i class="fas fa-calculator mr-2"></i>
                                Presupuesto #{{ $presupuesto->id }}
                            </h2>
                            <p class="text-sm text-boom-text-medium mt-1">
                                Creado el {{ $presupuesto->created_at->format('d/m/Y H:i') }} por {{ $presupuesto->usuarioRegistro->nombre ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            @if($presupuesto->puedeSerModificado())
                                <a href="{{ route('presupuestos-produccion.edit', $presupuesto->id) }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                    <i class="fas fa-edit mr-2"></i>Editar
                                </a>
                            @endif
                            <a href="{{ route('presupuestos-produccion.duplicar', $presupuesto->id) }}" 
                               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                <i class="fas fa-copy mr-2"></i>Duplicar
                            </a>
                            <a href="{{ route('presupuestos-produccion.index') }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                <i class="fas fa-arrow-left mr-2"></i>Volver
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Estado y Acciones Administrativas -->
                    <div class="bg-boom-cream-50 rounded-lg p-4 mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                            <div>
                                <h3 class="text-lg font-semibold text-boom-text-dark mb-2">Estado del Presupuesto</h3>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($presupuesto->estado == 'Borrador') bg-yellow-100 text-yellow-800
                                    @elseif($presupuesto->estado == 'Aprobado') bg-green-100 text-green-800
                                    @elseif($presupuesto->estado == 'Utilizado') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $presupuesto->estado }}
                                </span>
                            </div>
                            @if(Auth::user()->id_rol == 1)
                                <div class="flex space-x-2">
                                    @if($presupuesto->estado == 'Borrador')
                                        <form method="POST" action="{{ route('presupuestos-produccion.cambiar-estado', $presupuesto->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="estado" value="Aprobado">
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                                <i class="fas fa-check mr-2"></i>Aprobar
                                            </button>
                                        </form>
                                    @elseif($presupuesto->estado == 'Aprobado')
                                        <form method="POST" action="{{ route('presupuestos-produccion.cambiar-estado', $presupuesto->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="estado" value="Utilizado">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                                                <i class="fas fa-check-double mr-2"></i>Marcar como Utilizado
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Información General -->
                    <div class="bg-boom-cream-50 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                            <i class="fas fa-info-circle mr-2"></i>Información General
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-boom-text-dark mb-1">Tipo de Prenda</label>
                                <p class="text-lg font-semibold text-boom-text-dark">{{ $presupuesto->tipo_prenda }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-boom-text-dark mb-1">Tipo de Tela</label>
                                <p class="text-lg font-semibold text-boom-text-dark">{{ $presupuesto->tipo_tela }}</p>
                            </div>
                        </div>
                        @if($presupuesto->descripcion)
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-boom-text-dark mb-1">Descripción</label>
                                <p class="text-boom-text-medium">{{ $presupuesto->descripcion }}</p>
                            </div>
                        @endif
                        @if($presupuesto->pedido)
                            <div class="mt-4 p-3 bg-blue-100 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-link text-blue-600 mr-2"></i>
                                    <span class="font-medium text-blue-800">Asociado al Pedido #{{ $presupuesto->pedido->id_pedido }}</span>
                                </div>
                                <p class="text-sm text-blue-700 mt-1">
                                    Cliente: {{ $presupuesto->pedido->cliente->nombre ?? 'N/A' }} {{ $presupuesto->pedido->cliente->apellido ?? '' }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Desglose de Costos -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Costos Individuales - Materiales -->
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-box mr-2"></i>Costos Individuales - Materiales
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">1. Tela:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_tela, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">2. Cierre:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_cierre, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">3. Botón:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_boton, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">4. Bolsa:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_bolsa, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">5. Hilo:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_hilo, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">6a. Etiqueta Cinta:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_etiqueta_cinta, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">6b. Etiqueta Cartón:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_etiqueta_carton, 2) }}</span>
                                </div>
                                <hr class="border-blue-200">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-boom-text-dark">Subtotal Materiales:</span>
                                    <span class="text-lg font-bold text-blue-600">{{ $presupuesto->total_materiales_formateado }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Costos Individuales - Mano de Obra -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-users mr-2"></i>Costos Individuales - Mano de Obra
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">1. Tallerista:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_tallerista, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">2. Planchado:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_planchado, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">3. Ayudante:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_ayudante, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-boom-text-medium">4. Cortador:</span>
                                    <span class="font-semibold">Bs. {{ number_format($presupuesto->costo_cortador, 2) }}</span>
                                </div>
                                <hr class="border-green-200">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-boom-text-dark">Subtotal Mano de Obra:</span>
                                    <span class="text-lg font-bold text-green-600">{{ $presupuesto->total_mano_obra_formateado }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen Total -->
                    <div class="bg-boom-primary-light rounded-lg p-6 mb-6">
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-boom-text-dark mb-2">COSTO TOTAL DE PRODUCCIÓN</h3>
                            <div class="text-4xl font-bold text-boom-primary mb-4">{{ $presupuesto->costo_total_formateado }}</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div class="bg-white rounded-lg p-3">
                                    <div class="text-boom-text-medium">Materiales</div>
                                    <div class="font-bold text-blue-600">{{ $presupuesto->total_materiales_formateado }}</div>
                                    <div class="text-xs text-boom-text-medium">
                                        {{ number_format(($presupuesto->total_materiales / $presupuesto->costo_total) * 100, 1) }}% del total
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg p-3">
                                    <div class="text-boom-text-medium">Mano de Obra</div>
                                    <div class="font-bold text-green-600">{{ $presupuesto->total_mano_obra_formateado }}</div>
                                    <div class="text-xs text-boom-text-medium">
                                        {{ number_format(($presupuesto->total_mano_obra / $presupuesto->costo_total) * 100, 1) }}% del total
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                            <i class="fas fa-info mr-2"></i>Información del Registro
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-boom-text-dark">Registrado por:</span>
                                <p class="text-boom-text-medium">{{ $presupuesto->usuarioRegistro->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-boom-text-dark">Fecha de creación:</span>
                                <p class="text-boom-text-medium">{{ $presupuesto->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-boom-text-dark">Última actualización:</span>
                                <p class="text-boom-text-medium">{{ $presupuesto->updated_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-boom-text-dark">Estado actual:</span>
                                <p class="text-boom-text-medium">{{ $presupuesto->estado }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection