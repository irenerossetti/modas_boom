<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Avance de Producción') }} - Pedido #{{ $pedido->id_pedido }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Información del Pedido -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h3 class="font-semibold text-blue-900 mb-2">📦 Información del Pedido</h3>
                        <p class="text-sm text-blue-800">
                            <strong>Cliente:</strong> {{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}<br>
                            <strong>Estado:</strong> {{ $pedido->estado }}<br>
                            <strong>Total:</strong> Bs. {{ number_format($pedido->total, 2) }}
                        </p>
                    </div>

                    <!-- Formulario -->
                    <form method="POST" action="{{ route('pedidos.procesar-avance', $pedido->id_pedido) }}" class="space-y-6">
                        @csrf

                        <!-- Etapa -->
                        <div>
                            <label for="etapa" class="block text-sm font-medium text-gray-700">
                                Etapa de Producción <span class="text-red-500">*</span>
                            </label>
                            <select name="etapa" id="etapa" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Seleccione una etapa</option>
                                @foreach($etapas as $key => $etapa)
                                    <option value="{{ $key }}">{{ $etapa }}</option>
                                @endforeach
                            </select>
                            @error('etapa')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Operario -->
                        <div>
                            <label for="operario_id" class="block text-sm font-medium text-gray-700">
                                Operario que realizó el trabajo
                            </label>
                            <select name="operario_id" id="operario_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Sin asignar</option>
                                @foreach($operarios as $operario)
                                    <option value="{{ $operario->id_usuario }}">{{ $operario->nombre }}</option>
                                @endforeach
                            </select>
                            @error('operario_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Porcentaje de Avance -->
                        <div>
                            <label for="porcentaje_avance" class="block text-sm font-medium text-gray-700">
                                Porcentaje de Avance (%) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="porcentaje_avance" id="porcentaje_avance" 
                                min="0" max="100" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('porcentaje_avance')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Costo de Mano de Obra -->
                        <div>
                            <label for="costo_mano_obra" class="block text-sm font-medium text-gray-700">
                                Costo de Mano de Obra (Bs.) - Pago a Destajo
                            </label>
                            <input type="number" name="costo_mano_obra" id="costo_mano_obra" 
                                step="0.01" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="0.00">
                            @error('costo_mano_obra')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                💡 Ingrese el monto a pagar al operario por este trabajo específico
                            </p>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">
                                Descripción del Avance <span class="text-red-500">*</span>
                            </label>
                            <textarea name="descripcion" id="descripcion" rows="3" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Describa el trabajo realizado..."></textarea>
                            @error('descripcion')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="observaciones" class="block text-sm font-medium text-gray-700">
                                Observaciones Adicionales
                            </label>
                            <textarea name="observaciones" id="observaciones" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Observaciones opcionales..."></textarea>
                            @error('observaciones')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-3">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                ✅ Registrar Avance
                            </button>

                            <a href="{{ route('pedidos.show', $pedido->id_pedido) }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                ← Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
