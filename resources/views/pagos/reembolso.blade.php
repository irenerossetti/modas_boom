@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-undo-alt text-orange-500 mr-3"></i>
                Reembolsar Pago
            </h1>
            <p class="text-gray-600">Procesa el reembolso del pedido de forma segura</p>
        </div>

        <!-- Información del Pedido -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-shopping-bag text-blue-500 mr-2"></i>
                Información del Pedido
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm text-blue-600 font-medium">Número de Pedido</div>
                    <div class="text-xl font-bold text-blue-800">#{{ $pedido->id_pedido }}</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm text-green-600 font-medium">Cliente</div>
                    <div class="text-lg font-semibold text-green-800">{{ $pedido->nombre_completo_cliente }}</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="text-sm text-purple-600 font-medium">Total del Pedido</div>
                    <div class="text-xl font-bold text-purple-800">{{ $pedido->total_formateado }}</div>
                </div>
            </div>
        </div>

        <!-- Pagos Disponibles para Reembolso -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-credit-card text-green-500 mr-2"></i>
                Pagos Registrados
            </h2>
            
            @if($pagosActivos->count() > 0)
                @php
                    // Verificar si algún pago ya tiene reembolso
                    $pagosConReembolso = [];
                    foreach($pagosActivos as $pago) {
                        $reembolso = \App\Models\SolicitudReembolso::where('pago_id', $pago->id)->first();
                        if ($reembolso) {
                            $pagosConReembolso[$pago->id] = $reembolso;
                        }
                    }
                @endphp
                
                @if(count($pagosConReembolso) > 0)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <div class="flex-1">
                                <h4 class="font-medium text-green-800">Reembolsos Registrados</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    Este pedido ya tiene reembolsos registrados. Solo se permite un reembolso por pago.
                                </p>
                                @foreach($pagosConReembolso as $pagoId => $reembolso)
                                    <div class="mt-2 p-2 bg-white rounded border">
                                        <div class="text-sm">
                                            <strong>Pago ID {{ $pagoId }}:</strong> 
                                            @if($reembolso->metodo_reembolso === 'efectivo')
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                                    <i class="fas fa-money-bill-wave mr-1"></i>
                                                    Efectivo - Completado
                                                </span>
                                            @else
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                    <i class="fas fa-university mr-1"></i>
                                                    Transferencia - {{ $reembolso->banco }} ({{ $reembolso->numero_cuenta }})
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Alerta de pagos duplicados -->
                @if($pagosActivos->count() > 1)
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-orange-500 mt-1 mr-3"></i>
                            <div class="flex-1">
                                <h4 class="font-medium text-orange-800">Múltiples Pagos Detectados</h4>
                                <p class="text-sm text-orange-700 mt-1">
                                    Se detectaron {{ $pagosActivos->count() }} pagos para este pedido. 
                                    Si son duplicados, puedes limpiarlos automáticamente.
                                </p>
                                <button onclick="limpiarDuplicados({{ $pedido->id_pedido }})" 
                                        class="mt-3 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors"
                                        id="btn-limpiar">
                                    <i class="fas fa-broom mr-2"></i>
                                    Limpiar Duplicados
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Debug temporal -->
                @if(config('app.debug'))
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-2 mb-4 text-xs">
                        <strong>Debug:</strong> {{ $pagosActivos->count() }} pagos encontrados
                        @foreach($pagosActivos as $debugPago)
                            <br>ID: {{ $debugPago->id }} | Monto: {{ $debugPago->monto }} | Método: {{ $debugPago->metodo }} | Fecha: {{ $debugPago->fecha_pago }} | Ref: {{ $debugPago->referencia ?? 'N/A' }} | Anulado: {{ $debugPago->anulado ? 'Sí' : 'No' }}
                        @endforeach
                    </div>
                @endif
                
                <div class="space-y-3" id="pagos-container">
                    @foreach($pagosActivos as $pago)
                    @php
                        $tieneReembolso = isset($pagosConReembolso[$pago->id]);
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4 {{ $tieneReembolso ? 'bg-gray-50 cursor-not-allowed' : 'hover:border-orange-300 transition-colors cursor-pointer' }} pago-item" 
                         data-pago-id="{{ $pago->id }}" 
                         data-monto="{{ $pago->monto }}"
                         data-metodo="{{ $pago->metodo }}"
                         data-tiene-reembolso="{{ $tieneReembolso ? 'true' : 'false' }}">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-green-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-lg">Bs. {{ number_format($pago->monto, 2) }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ ucfirst($pago->metodo) }} • {{ $pago->fecha_pago->format('d/m/Y H:i') }}
                                    </div>
                                    @if($pago->referencia)
                                        <div class="text-xs text-gray-500">Ref: {{ $pago->referencia }}</div>
                                    @endif
                                    <div class="text-xs text-gray-400">ID: {{ $pago->id }}</div>
                                    @if($tieneReembolso)
                                        @php $reembolso = $pagosConReembolso[$pago->id]; @endphp
                                        <div class="mt-2">
                                            @if($reembolso->metodo_reembolso === 'efectivo')
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Reembolsado en Efectivo
                                                </span>
                                            @else

                                                <div class="flex items-center gap-2">
                                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        Transferencia Pendiente
                                                    </span>
                                                    @if(Auth::user()->id_rol == 1) <!-- Solo Admin -->
                                                        <button onclick="event.stopPropagation(); marcarReembolsoCompletado({{ $reembolso->id }})" 
                                                                class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs transition-colors"
                                                                title="Marcar como completado">
                                                            <i class="fas fa-check mr-1"></i> Completar
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Registrado por</div>
                                <div class="font-medium">{{ $pago->registradoPor->nombre ?? 'N/A' }}</div>
                                @if($tieneReembolso)
                                    <div class="text-xs text-red-500 mt-1">
                                        <i class="fas fa-ban mr-1"></i>
                                        Ya reembolsado
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-circle text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-600">No hay pagos activos para reembolsar en este pedido.</p>
                </div>
            @endif
        </div>

        <!-- Formulario de Reembolso -->
        @if($pagosActivos->count() > 0)
        <div class="bg-white rounded-xl shadow-lg p-6" id="formulario-reembolso" style="display: none;">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-file-alt text-orange-500 mr-2"></i>
                Datos del Reembolso
            </h2>

            <form id="form-reembolso" method="POST">
                @csrf
                <input type="hidden" id="pago_id" name="pago_id" value="">
                
                <!-- Campos hidden para asegurar que siempre se envíen -->
                <input type="hidden" id="banco_hidden" name="banco" value="">
                <input type="hidden" id="numero_cuenta_hidden" name="numero_cuenta" value="">
                
                <!-- Tipo de Reembolso -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Reembolso</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="tipo-reembolso border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors" data-tipo="error_sistema">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-bug text-red-500 mr-2"></i>
                                <span class="font-medium">Error del Sistema</span>
                            </div>
                            <p class="text-sm text-gray-600">Pago duplicado o error técnico</p>
                        </div>
                        <div class="tipo-reembolso border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors" data-tipo="pedido_cancelado">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-times-circle text-orange-500 mr-2"></i>
                                <span class="font-medium">Pedido Cancelado</span>
                            </div>
                            <p class="text-sm text-gray-600">Cliente canceló el pedido</p>
                        </div>
                        <div class="tipo-reembolso border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors" data-tipo="solicitud_cliente">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-user-times text-blue-500 mr-2"></i>
                                <span class="font-medium">Solicitud del Cliente</span>
                            </div>
                            <p class="text-sm text-gray-600">Cliente solicitó reembolso</p>
                        </div>
                    </div>
                    <input type="hidden" id="tipo_reembolso" name="tipo_reembolso" required>
                </div>

                <!-- Motivo Detallado -->
                <div class="mb-6">
                    <label for="motivo_detallado" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo Detallado <span class="text-red-500">*</span>
                    </label>
                    <textarea id="motivo_detallado" name="motivo_detallado" rows="4" 
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                              placeholder="Describe el motivo específico del reembolso..." required></textarea>
                </div>

                <!-- Datos del Beneficiario -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-blue-500 mr-2"></i>
                        Datos del Beneficiario del Reembolso
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="beneficiario_nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="beneficiario_nombre" name="beneficiario_nombre" 
                                   value="{{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label for="beneficiario_ci" class="block text-sm font-medium text-gray-700 mb-2">
                                CI/NIT <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="beneficiario_ci" name="beneficiario_ci" 
                                   value="{{ $pedido->cliente->ci_nit }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label for="beneficiario_telefono" class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="beneficiario_telefono" name="beneficiario_telefono" 
                                   value="{{ $pedido->cliente->telefono }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label for="beneficiario_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>
                            <input type="email" id="beneficiario_email" name="beneficiario_email" 
                                   value="{{ $pedido->cliente->email }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Método de Reembolso -->
                <div class="mb-6" id="metodo-reembolso-section">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Método de Reembolso</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="metodo-reembolso border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors" data-metodo="efectivo">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                <span class="font-medium">Efectivo</span>
                            </div>
                            <p class="text-sm text-gray-600">Reembolso en efectivo en oficina</p>
                        </div>
                        <div class="metodo-reembolso border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors" data-metodo="transferencia">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-university text-blue-500 mr-2"></i>
                                <span class="font-medium">Transferencia Bancaria</span>
                            </div>
                            <p class="text-sm text-gray-600">Transferencia a cuenta bancaria</p>
                        </div>
                    </div>
                    <input type="hidden" id="metodo_reembolso" name="metodo_reembolso" required>
                </div>

                <!-- Datos Bancarios (solo si es transferencia) -->
                <div class="mb-6" id="datos-bancarios" style="display: none;">
                    <h4 class="text-md font-medium text-gray-800 mb-3">Datos Bancarios</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="banco" class="block text-sm font-medium text-gray-700 mb-2">Banco</label>
                            <input type="text" id="banco" name="banco" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   placeholder="Nombre del banco">
                        </div>
                        <div>
                            <label for="numero_cuenta" class="block text-sm font-medium text-gray-700 mb-2">Número de Cuenta</label>
                            <input type="text" id="numero_cuenta" name="numero_cuenta" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   placeholder="Número de cuenta bancaria">
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-between items-center pt-6 border-t">
                    <a href="{{ route('pedidos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver a Pedidos
                    </a>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-lg font-medium transition-colors" id="btn-procesar">
                        <i class="fas fa-undo mr-2"></i>
                        Procesar Reembolso
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Selección de pago
    document.querySelectorAll('.pago-item').forEach(item => {
        item.addEventListener('click', function() {
            // Verificar si ya tiene reembolso
            if (this.dataset.tieneReembolso === 'true') {
                alert('Este pago ya tiene un reembolso registrado. Solo se permite un reembolso por pago.');
                return;
            }
            
            // Remover selección anterior
            document.querySelectorAll('.pago-item').forEach(p => {
                p.classList.remove('border-orange-500', 'bg-orange-50');
                p.classList.add('border-gray-200');
            });
            
            // Seleccionar actual
            this.classList.remove('border-gray-200');
            this.classList.add('border-orange-500', 'bg-orange-50');
            
            // Establecer datos
            document.getElementById('pago_id').value = this.dataset.pagoId;
            
            // Mostrar formulario
            document.getElementById('formulario-reembolso').style.display = 'block';
            document.getElementById('formulario-reembolso').scrollIntoView({ behavior: 'smooth' });
        });
    });
    
    // Selección de tipo de reembolso
    document.querySelectorAll('.tipo-reembolso').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.tipo-reembolso').forEach(t => {
                t.classList.remove('border-orange-500', 'bg-orange-50');
                t.classList.add('border-gray-200');
            });
            
            this.classList.remove('border-gray-200');
            this.classList.add('border-orange-500', 'bg-orange-50');
            
            document.getElementById('tipo_reembolso').value = this.dataset.tipo;
        });
    });
    
    // Selección de método de reembolso
    document.querySelectorAll('.metodo-reembolso').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.metodo-reembolso').forEach(m => {
                m.classList.remove('border-orange-500', 'bg-orange-50');
                m.classList.add('border-gray-200');
            });
            
            this.classList.remove('border-gray-200');
            this.classList.add('border-orange-500', 'bg-orange-50');
            
            document.getElementById('metodo_reembolso').value = this.dataset.metodo;
            
            // Mostrar/ocultar datos bancarios
            const datosBancarios = document.getElementById('datos-bancarios');
            if (this.dataset.metodo === 'transferencia') {
                datosBancarios.style.display = 'block';
                document.getElementById('banco').required = true;
                document.getElementById('numero_cuenta').required = true;
            } else {
                datosBancarios.style.display = 'none';
                document.getElementById('banco').required = false;
                document.getElementById('numero_cuenta').required = false;
                // Limpiar campos hidden cuando no es transferencia
                document.getElementById('banco_hidden').value = '';
                document.getElementById('numero_cuenta_hidden').value = '';
            }
        });
    });
    
    // Sincronizar campos bancarios visibles con hidden
    document.getElementById('banco').addEventListener('input', function() {
        document.getElementById('banco_hidden').value = this.value;
    });
    
    document.getElementById('numero_cuenta').addEventListener('input', function() {
        document.getElementById('numero_cuenta_hidden').value = this.value;
    });
    
    // Envío del formulario
    document.getElementById('form-reembolso').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar que se haya seleccionado un pago
        const pagoId = document.getElementById('pago_id').value;
        if (!pagoId) {
            alert('Por favor, selecciona un pago para reembolsar');
            return;
        }
        
        // Validar que se haya seleccionado tipo de reembolso
        const tipoReembolso = document.getElementById('tipo_reembolso').value;
        if (!tipoReembolso) {
            alert('Por favor, selecciona el tipo de reembolso');
            return;
        }
        
        // Validar que se haya seleccionado método de reembolso
        const metodoReembolso = document.getElementById('metodo_reembolso').value;
        if (!metodoReembolso) {
            alert('Por favor, selecciona el método de reembolso');
            return;
        }
        
        const btn = document.getElementById('btn-procesar');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
        btn.disabled = true;
        
        const formData = new FormData(this);
        
        // Debug: mostrar datos que se van a enviar
        console.log('Datos del formulario:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
        
        fetch(`/pagos/${formData.get('pago_id')}/reembolsar`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Redirigir a página de éxito con tipo de procesamiento
                const tipo = data.tipo_procesamiento || 'completado';
                window.location.href = `/reembolso-exitoso?pedido={{ $pedido->id_pedido }}&tipo=${tipo}`;
            } else {
                alert('Error: ' + data.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar el reembolso');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
    
    // Función para limpiar pagos duplicados
    window.limpiarDuplicados = function(pedidoId) {
        const btn = document.getElementById('btn-limpiar');
        const originalText = btn.innerHTML;
        
        if (!confirm('¿Estás seguro de que deseas limpiar los pagos duplicados? Esta acción anulará los pagos duplicados manteniendo solo el original.')) {
            return;
        }
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Limpiando...';
        btn.disabled = true;
        
        fetch(`/pagos/limpiar-duplicados/${pedidoId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                if (data.pagos_limpiados > 0) {
                    location.reload(); // Recargar para mostrar los cambios
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al limpiar duplicados');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    };
    // Función para marcar reembolso como completado (desde esta vista)
    window.marcarReembolsoCompletado = function(reembolsoId) {
        if (!confirm('¿Está seguro de que desea marcar este reembolso como completado?\n\nEsta acción confirmará que la transferencia bancaria fue procesada y ANULARÁ el pago automáticamente.')) {
            return;
        }
        
        // Crear formulario dinámico
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/reembolsos/' + reembolsoId + '/completar';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrf);
        
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'PATCH';
        form.appendChild(method);
        
        document.body.appendChild(form);
        form.submit();
    };
});
</script>
@endpush
@endsection