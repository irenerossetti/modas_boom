<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-edit mr-2"></i>
                Editar Pedido #{{ $pedido->id_pedido }}
            </h1>
            <div class="flex space-x-2">
                <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-eye mr-1"></i>
                    Ver Detalles
                </a>
                <a href="{{ route('pedidos.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Volver
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Oops! Hay algunos errores:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Información actual del pedido -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">
                <i class="fas fa-info-circle mr-2"></i>
                Información Actual
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="font-medium text-blue-700">Estado Actual:</span>
                    <div class="mt-1">
                        <span class="px-3 py-1 text-sm font-medium rounded-full {{ $pedido->estado_color }}">
                            <i class="{{ $pedido->estado_icono }} mr-1"></i>
                            {{ $pedido->estado }}
                        </span>
                    </div>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Fecha de Creación:</span>
                    <div class="mt-1 text-blue-600">{{ $pedido->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Última Modificación:</span>
                    <div class="mt-1 text-blue-600">{{ $pedido->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <form action="{{ route('pedidos.update', $pedido->id_pedido) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Selección de Cliente -->
                    <div class="md:col-span-2">
                        <label for="id_cliente" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-1"></i>
                            Cliente *
                        </label>
                        <select name="id_cliente" id="id_cliente" 
                                class="form-select block w-full rounded-md shadow-sm @error('id_cliente') border-red-500 @enderror" 
                                required>
                            <option value="">Seleccione un cliente...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" 
                                        {{ (old('id_cliente', $pedido->id_cliente) == $cliente->id) ? 'selected' : '' }}>
                                    {{ $cliente->nombre }} {{ $cliente->apellido }} - {{ $cliente->ci_nit }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_cliente')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estado del Pedido -->
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-flag mr-1"></i>
                            Estado *
                        </label>
                        <select name="estado" id="estado" 
                                class="form-select block w-full rounded-md shadow-sm @error('estado') border-red-500 @enderror" 
                                required>
                            @foreach($estados as $valor => $etiqueta)
                                <option value="{{ $valor }}" 
                                        {{ (old('estado', $pedido->estado) == $valor) ? 'selected' : '' }}>
                                    {{ $etiqueta }}
                                </option>
                            @endforeach
                        </select>
                        @error('estado')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Cambiar el estado según el progreso del pedido
                        </p>
                    </div>

                    <!-- Total del Pedido -->
                    <div>
                        <label for="total" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-dollar-sign mr-1"></i>
                            Total (Bs.)
                        </label>
                        <input type="number" name="total" id="total" step="0.01" min="0"
                               value="{{ old('total', $pedido->total) }}"
                               class="form-input block w-full rounded-md shadow-sm @error('total') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('total')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Monto total del pedido
                        </p>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-6 rounded transition-colors duration-200">
                        <i class="fas fa-save mr-1"></i>
                        Actualizar Pedido
                    </button>
                </div>
            </form>
        </div>

        <!-- Advertencias sobre cambios de estado -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Importante sobre Estados
            </h3>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li><i class="fas fa-info mr-2"></i><strong>En proceso:</strong> Pedido recién creado, pendiente de asignación</li>
                <li><i class="fas fa-info mr-2"></i><strong>Asignado:</strong> Pedido asignado a un operario específico</li>
                <li><i class="fas fa-info mr-2"></i><strong>En producción:</strong> Trabajo iniciado por el operario</li>
                <li><i class="fas fa-info mr-2"></i><strong>Terminado:</strong> Trabajo completado, listo para entrega</li>
                <li><i class="fas fa-info mr-2"></i><strong>Entregado:</strong> Pedido entregado al cliente</li>
                <li><i class="fas fa-info mr-2"></i><strong>Cancelado:</strong> Pedido cancelado (no se puede modificar)</li>
            </ul>
        </div>
    </div>

    @push('scripts')
    <script>
        // Mostrar advertencia al cambiar a estados finales
        document.getElementById('estado').addEventListener('change', function() {
            const estado = this.value;
            const estadosFinales = ['Entregado', 'Cancelado'];
            
            if (estadosFinales.includes(estado)) {
                const confirmacion = confirm(
                    `¿Está seguro de cambiar el estado a "${estado}"?\n\n` +
                    'Una vez cambiado a este estado, no podrá modificar el pedido posteriormente.'
                );
                
                if (!confirmacion) {
                    // Restaurar el estado anterior
                    this.value = '{{ $pedido->estado }}';
                }
            }
        });

        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const cliente = document.getElementById('id_cliente').value;
            const estado = document.getElementById('estado').value;
            
            if (!cliente) {
                e.preventDefault();
                alert('Por favor seleccione un cliente para el pedido.');
                document.getElementById('id_cliente').focus();
                return false;
            }
            
            if (!estado) {
                e.preventDefault();
                alert('Por favor seleccione un estado para el pedido.');
                document.getElementById('estado').focus();
                return false;
            }
        });
    </script>
    @endpush
</x-app-layout>