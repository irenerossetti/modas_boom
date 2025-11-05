@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Pedido
            </h1>
            <a href="{{ route('pedidos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver
            </a>
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

        @if(isset($clienteSeleccionado) && $clienteSeleccionado)
            @php
                $cliente = $clientes->find($clienteSeleccionado);
            @endphp
            @if($cliente)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">
                        <i class="fas fa-user-check mr-2"></i>
                        Cliente Preseleccionado
                    </h3>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white text-lg font-bold mr-4">
                            {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-semibold text-green-800">
                                {{ $cliente->nombre }} {{ $cliente->apellido }}
                            </div>
                            <div class="text-sm text-green-600">
                                CI/NIT: {{ $cliente->ci_nit }}
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-green-700 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Este cliente ya está seleccionado en el formulario. Puedes cambiarlo si es necesario.
                    </p>
                </div>
            @endif
        @endif

        @if(isset($productoSeleccionado) && $productoSeleccionado['nombre'])
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-2">
                    <i class="fas fa-tshirt mr-2"></i>
                    Producto Seleccionado del Catálogo
                </h3>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white text-lg font-bold mr-4">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-blue-800">
                            {{ $productoSeleccionado['nombre'] }}
                        </div>
                        <div class="text-sm text-blue-600">
                            Precio: Bs. {{ number_format($productoSeleccionado['precio'], 2) }}
                        </div>
                        <div class="text-sm text-blue-600 capitalize">
                            Categoría: {{ $productoSeleccionado['categoria'] }}
                        </div>
                    </div>
                </div>
                <p class="text-sm text-blue-700 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    El precio y descripción ya están configurados. Puedes modificarlos si es necesario.
                </p>
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow">
            <form action="{{ route('pedidos.store') }}" method="POST">
                @csrf
                
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
                                        {{ (old('id_cliente', $clienteSeleccionado ?? '') == $cliente->id) ? 'selected' : '' }}>
                                    {{ $cliente->nombre }} {{ $cliente->apellido }} - {{ $cliente->ci_nit }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_cliente')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Seleccione el cliente para quien es el pedido
                        </p>
                    </div>

                    <!-- Total del Pedido -->
                    <div>
                        <label for="total" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-dollar-sign mr-1"></i>
                            Total (Bs.)
                        </label>
                        <input type="number" name="total" id="total" step="0.01" min="0"
                               value="{{ old('total', $productoSeleccionado['precio'] ?? '') }}"
                               class="form-input block w-full rounded-md shadow-sm @error('total') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('total')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ isset($productoSeleccionado['precio']) && $productoSeleccionado['precio'] ? 'Precio sugerido del catálogo - Puedes modificarlo' : 'Opcional - Puede especificarse más tarde' }}
                        </p>
                    </div>

                    <!-- Estado (solo informativo) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-flag mr-1"></i>
                            Estado Inicial
                        </label>
                        <div class="form-input block w-full rounded-md shadow-sm bg-gray-50">
                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-clock mr-1"></i>
                                En proceso
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            El pedido se creará con estado "En proceso"
                        </p>
                    </div>

                    <!-- Descripción/Notas -->
                    <div class="md:col-span-2">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sticky-note mr-1"></i>
                            Descripción o Notas
                        </label>
                        <textarea name="descripcion" id="descripcion" rows="4"
                                  class="form-textarea block w-full rounded-md shadow-sm @error('descripcion') border-red-500 @enderror"
                                  placeholder="Detalles del pedido, especificaciones, notas importantes...">{{ old('descripcion', isset($productoSeleccionado['nombre']) ? 'Pedido de: ' . $productoSeleccionado['nombre'] . (isset($productoSeleccionado['categoria']) ? ' (Categoría: ' . $productoSeleccionado['categoria'] . ')' : '') : '') }}</textarea>
                        @error('descripcion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ isset($productoSeleccionado['nombre']) && $productoSeleccionado['nombre'] ? 'Descripción prellenada del catálogo - Puedes modificarla' : 'Opcional - Información adicional sobre el pedido' }}
                        </p>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('pedidos.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-6 rounded transition-colors duration-200">
                        <i class="fas fa-save mr-1"></i>
                        Crear Pedido
                    </button>
                </div>
            </form>
        </div>

        <!-- Información adicional -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                Información sobre Pedidos
            </h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li><i class="fas fa-check mr-2"></i>El pedido se creará con estado "En proceso"</li>
                <li><i class="fas fa-check mr-2"></i>Podrá modificar los detalles posteriormente</li>
                <li><i class="fas fa-check mr-2"></i>Los administradores pueden asignar operarios</li>
                <li><i class="fas fa-check mr-2"></i>Todos los cambios quedan registrados en la bitácora</li>
            </ul>
        </div>
    </div>

    @push('scripts')
    <script>
        // Mejorar la experiencia del selector de cliente
        document.getElementById('id_cliente').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                // Aquí se podría agregar lógica para mostrar información adicional del cliente
                console.log('Cliente seleccionado:', selectedOption.text);
            }
        });

        // Validación del lado del cliente
        document.querySelector('form').addEventListener('submit', function(e) {
            const cliente = document.getElementById('id_cliente').value;
            
            if (!cliente) {
                e.preventDefault();
                alert('Por favor seleccione un cliente para el pedido.');
                document.getElementById('id_cliente').focus();
                return false;
            }
        });
    </script>
    @endpush
@endsection
