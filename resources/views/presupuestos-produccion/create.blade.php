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
                                Nuevo Presupuesto de Producción
                            </h2>
                            @if($pedido)
                                <p class="text-sm text-boom-text-medium mt-1">
                                    Para pedido #{{ $pedido->id_pedido }} - {{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}
                                </p>
                            @endif
                        </div>
                        <a href="{{ route('presupuestos-produccion.index') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                    </div>

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('presupuestos-produccion.store') }}" id="presupuestoForm">
                        @csrf
                        
                        @if($pedido)
                            <input type="hidden" name="id_pedido" value="{{ $pedido->id_pedido }}">
                        @endif

                        <!-- Información General -->
                        <div class="bg-boom-cream-50 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-info-circle mr-2"></i>Información General
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="tipo_prenda" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        Tipo de Prenda <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="tipo_prenda" id="tipo_prenda" 
                                           value="{{ old('tipo_prenda') }}" 
                                           list="tipos_prenda"
                                           class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           placeholder="Ej: Pantalón Palazzo, Blusa Casual..."
                                           required>
                                    <datalist id="tipos_prenda">
                                        @foreach($tiposPrenda as $tipo)
                                            <option value="{{ $tipo }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label for="tipo_tela" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        Tipo de Tela <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="tipo_tela" id="tipo_tela" 
                                           value="{{ old('tipo_tela') }}" 
                                           list="tipos_tela"
                                           class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           placeholder="Ej: Algodón, Poliéster, Seda..."
                                           required>
                                    <datalist id="tipos_tela">
                                        @foreach($tiposTela as $tipo)
                                            <option value="{{ $tipo }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="descripcion" class="block text-sm font-medium text-boom-text-dark mb-1">
                                    Descripción Adicional
                                </label>
                                <textarea name="descripcion" id="descripcion" rows="3" 
                                          class="w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                          placeholder="Detalles adicionales del presupuesto...">{{ old('descripcion') }}</textarea>
                            </div>
                        </div>

                        <!-- Costos Individuales - Materiales -->
                        <div class="bg-blue-50 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-box mr-2"></i>Costos Individuales - Materiales
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- 1. Tela -->
                                <div>
                                    <label for="costo_tela" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        1. Tela (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_tela" id="costo_tela" 
                                           value="{{ old('costo_tela', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                                <!-- 2. Cierre -->
                                <div>
                                    <label for="costo_cierre" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        2. Cierre (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_cierre" id="costo_cierre" 
                                           value="{{ old('costo_cierre', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                                <!-- 3. Botón -->
                                <div>
                                    <label for="costo_boton" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        3. Botón (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_boton" id="costo_boton" 
                                           value="{{ old('costo_boton', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                                <!-- 4. Bolsa -->
                                <div>
                                    <label for="costo_bolsa" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        4. Bolsa (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_bolsa" id="costo_bolsa" 
                                           value="{{ old('costo_bolsa', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                                <!-- 5. Hilo -->
                                <div>
                                    <label for="costo_hilo" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        5. Hilo (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_hilo" id="costo_hilo" 
                                           value="{{ old('costo_hilo', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                                <!-- 6. Etiqueta (cinta y cartón) -->
                                <div>
                                    <label for="costo_etiqueta_cinta" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        6a. Etiqueta Cinta (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_etiqueta_cinta" id="costo_etiqueta_cinta" 
                                           value="{{ old('costo_etiqueta_cinta', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                                <div>
                                    <label for="costo_etiqueta_carton" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        6b. Etiqueta Cartón (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_etiqueta_carton" id="costo_etiqueta_carton" 
                                           value="{{ old('costo_etiqueta_carton', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-blue-100 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-boom-text-dark">Total Materiales:</span>
                                    <span id="total_materiales" class="text-lg font-bold text-blue-600">Bs. 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Costos Individuales - Mano de Obra -->
                        <div class="bg-green-50 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold text-boom-text-dark mb-4">
                                <i class="fas fa-users mr-2"></i>Costos Individuales - Mano de Obra
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- 1. Tallerista -->
                                <div>
                                    <label for="costo_tallerista" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        1. Tallerista (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_tallerista" id="costo_tallerista" 
                                           value="{{ old('costo_tallerista', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                                <!-- 2. Planchado -->
                                <div>
                                    <label for="costo_planchado" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        2. Planchado (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_planchado" id="costo_planchado" 
                                           value="{{ old('costo_planchado', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                                <!-- 3. Ayudante -->
                                <div>
                                    <label for="costo_ayudante" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        3. Ayudante (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_ayudante" id="costo_ayudante" 
                                           value="{{ old('costo_ayudante', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                                <!-- 4. Cortador -->
                                <div>
                                    <label for="costo_cortador" class="block text-sm font-medium text-boom-text-dark mb-1">
                                        4. Cortador (Bs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="costo_cortador" id="costo_cortador" 
                                           value="{{ old('costo_cortador', 0) }}" 
                                           step="0.01" min="0"
                                           class="costo-input w-full px-3 py-2 border border-boom-cream-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-boom-primary"
                                           required>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-green-100 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-boom-text-dark">Total Mano de Obra:</span>
                                    <span id="total_mano_obra" class="text-lg font-bold text-green-600">Bs. 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen Total -->
                        <div class="bg-boom-primary-light rounded-lg p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-boom-text-dark">COSTO TOTAL DE PRODUCCIÓN:</span>
                                <span id="costo_total" class="text-2xl font-bold text-boom-primary">Bs. 0.00</span>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4">
                            <a href="{{ route('presupuestos-produccion.index') }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-300 text-center">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                            <button type="submit" 
                                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg border-2 border-green-800 transition-colors duration-300">
                                <i class="fas fa-save mr-2 text-white"></i>Guardar Presupuesto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para calcular totales
            function calcularTotales() {
                // Calcular total de materiales
                const costosMateriales = [
                    'costo_tela', 'costo_cierre', 'costo_boton', 'costo_bolsa', 
                    'costo_hilo', 'costo_etiqueta_cinta', 'costo_etiqueta_carton'
                ];
                
                let totalMateriales = 0;
                costosMateriales.forEach(function(campo) {
                    const valor = parseFloat(document.getElementById(campo).value) || 0;
                    totalMateriales += valor;
                });

                // Calcular total de mano de obra
                const costosManoObra = [
                    'costo_tallerista', 'costo_planchado', 'costo_ayudante', 'costo_cortador'
                ];
                
                let totalManoObra = 0;
                costosManoObra.forEach(function(campo) {
                    const valor = parseFloat(document.getElementById(campo).value) || 0;
                    totalManoObra += valor;
                });

                // Calcular total general
                const costoTotal = totalMateriales + totalManoObra;

                // Actualizar displays
                document.getElementById('total_materiales').textContent = 'Bs. ' + totalMateriales.toFixed(2);
                document.getElementById('total_mano_obra').textContent = 'Bs. ' + totalManoObra.toFixed(2);
                document.getElementById('costo_total').textContent = 'Bs. ' + costoTotal.toFixed(2);
            }

            // Agregar event listeners a todos los campos de costo
            const camposCosto = document.querySelectorAll('.costo-input');
            camposCosto.forEach(function(campo) {
                campo.addEventListener('input', calcularTotales);
                campo.addEventListener('change', calcularTotales);
            });

            // Calcular totales iniciales
            calcularTotales();

            // Validación del formulario
            document.getElementById('presupuestoForm').addEventListener('submit', function(e) {
                const costoTotal = parseFloat(document.getElementById('costo_total').textContent.replace('Bs. ', ''));
                
                if (costoTotal <= 0) {
                    e.preventDefault();
                    alert('El costo total del presupuesto debe ser mayor a cero.');
                    return false;
                }
            });
        });
    </script>
    @endpush
@endsection