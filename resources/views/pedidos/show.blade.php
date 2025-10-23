<x-app-layout>
    <div class="p-2 sm:p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 lg:mb-6 space-y-3 sm:space-y-0">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-shopping-bag mr-2"></i>
                <span class="hidden sm:inline">Pedido #{{ $pedido->id_pedido }}</span>
                <span class="sm:hidden">#{{ $pedido->id_pedido }}</span>
            </h1>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                @if($pedido->puedeSerEditado())
                    <a href="{{ route('pedidos.edit', $pedido->id_pedido) }}" 
                       class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                        <i class="fas fa-edit mr-1"></i>
                        Editar
                    </a>
                @endif
                
                <a href="{{ route('pedidos.historial', $pedido->id_pedido) }}" 
                   class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                    <i class="fas fa-history mr-1"></i>
                    Historial
                </a>
                
                <a href="{{ route('pedidos.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Volver
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
            <!-- Información Principal del Pedido -->
            <div class="lg:col-span-2">
                <div class="bg-white p-3 sm:p-6 rounded-lg shadow mb-4 lg:mb-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-boom-text-dark mb-3 sm:mb-4">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="hidden sm:inline">Información del Pedido</span>
                        <span class="sm:hidden">Información</span>
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Pedido</label>
                            <div class="text-2xl font-bold text-boom-primary">
                                #{{ $pedido->id_pedido }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado Actual</label>
                            <div class="mt-1">
                                <span class="px-4 py-2 text-sm font-medium rounded-full {{ $pedido->estado_color }}">
                                    <i class="{{ $pedido->estado_icono }} mr-2"></i>
                                    {{ $pedido->estado }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total del Pedido</label>
                            <div class="text-xl font-semibold text-boom-text-dark">
                                {{ $pedido->total_formateado }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Creación</label>
                            <div class="text-boom-text-dark">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $pedido->created_at->format('d/m/Y') }}
                                <span class="text-gray-500 ml-2">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $pedido->created_at->format('H:i') }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                {{ $pedido->created_at->diffForHumans() }}
                            </div>
                        </div>
                        
                        @if($pedido->updated_at != $pedido->created_at)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Última Modificación</label>
                            <div class="text-boom-text-dark">
                                <i class="fas fa-edit mr-1"></i>
                                {{ $pedido->updated_at->format('d/m/Y H:i') }}
                                <span class="text-sm text-gray-500 ml-2">
                                    ({{ $pedido->updated_at->diffForHumans() }})
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Productos del Pedido -->
                @if($pedido->prendas->count() > 0)
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <h2 class="text-xl font-semibold text-boom-text-dark mb-4">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Productos del Pedido
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($pedido->prendas as $prenda)
                        <div class="flex items-center justify-between p-4 bg-boom-cream-100 rounded-lg border border-boom-cream-300">
                            <div class="flex items-center space-x-4">
                                <img src="{{ asset($prenda->imagen) }}" 
                                     alt="{{ $prenda->nombre }}" 
                                     class="w-16 h-16 object-cover rounded-lg">
                                <div>
                                    <h4 class="font-semibold text-boom-text-dark">{{ $prenda->nombre }}</h4>
                                    <p class="text-sm text-boom-text-medium">{{ $prenda->categoria }}</p>
                                    <div class="flex items-center space-x-4 text-sm text-boom-text-medium mt-1">
                                        <span>
                                            <i class="fas fa-cubes mr-1"></i>
                                            {{ $prenda->pivot->cantidad }} unidades
                                        </span>
                                        @if($prenda->pivot->talla)
                                        <span>
                                            <i class="fas fa-ruler mr-1"></i>
                                            {{ $prenda->pivot->talla }}
                                        </span>
                                        @endif
                                        @if($prenda->pivot->color)
                                        <span>
                                            <i class="fas fa-palette mr-1"></i>
                                            {{ $prenda->pivot->color }}
                                        </span>
                                        @endif
                                    </div>
                                    @if($prenda->pivot->observaciones)
                                    <p class="text-xs text-boom-text-medium mt-1">
                                        <i class="fas fa-comment mr-1"></i>
                                        {{ $prenda->pivot->observaciones }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-boom-rose-dark">
                                    Bs. {{ number_format($prenda->pivot->precio_unitario * ($prenda->pivot->cantidad / 12), 2) }}
                                </p>
                                <p class="text-sm text-boom-text-medium">
                                    Bs. {{ number_format($prenda->pivot->precio_unitario, 2) }} c/docena
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Resumen del pedido -->
                    <div class="mt-6 pt-4 border-t border-boom-cream-300">
                        <div class="flex justify-between items-center">
                            <div class="text-boom-text-medium">
                                <p><strong>Total de productos:</strong> {{ $pedido->prendas->count() }}</p>
                                <p><strong>Total de unidades:</strong> {{ $pedido->prendas->sum('pivot.cantidad') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-boom-rose-dark">
                                    {{ $pedido->total_formateado }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Acciones Disponibles -->
                @if($pedido->puedeSerEditado() || $pedido->puedeSerCancelado() || $pedido->puedeSerAsignado())
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-boom-text-dark mb-4">
                        <i class="fas fa-cogs mr-2"></i>
                        Acciones Disponibles
                    </h2>
                    
                    <div class="flex flex-wrap gap-3">
                        @if($pedido->puedeSerEditado())
                            <a href="{{ route('pedidos.edit', $pedido->id_pedido) }}" 
                               class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                <i class="fas fa-edit mr-1"></i>
                                Editar Pedido
                            </a>
                        @endif
                        
                        @if(Auth::user()->rol && Auth::user()->rol->nombre === 'Administrador' && $pedido->puedeSerAsignado())
                            <button onclick="mostrarModalAsignar()" 
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                <i class="fas fa-user-plus mr-1"></i>
                                Asignar Operario
                            </button>
                        @endif
                        
                        @if($pedido->puedeSerCancelado())
                            <form action="{{ route('pedidos.destroy', $pedido->id_pedido) }}" method="POST" class="inline" 
                                  onsubmit="return confirm('¿Está seguro de que desea cancelar este pedido?\n\nEsta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    <i class="fas fa-times mr-1"></i>
                                    Cancelar Pedido
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Información del Cliente -->
            <div>
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <h2 class="text-xl font-semibold text-boom-text-dark mb-4">
                        <i class="fas fa-user mr-2"></i>
                        Información del Cliente
                    </h2>
                    
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-boom-primary rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-3">
                            {{ strtoupper(substr($pedido->cliente->nombre, 0, 1)) }}
                        </div>
                        <h3 class="text-lg font-semibold text-boom-text-dark">
                            {{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}
                        </h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-id-card w-5 text-gray-500 mr-3"></i>
                            <div>
                                <div class="text-sm text-gray-500">CI/NIT</div>
                                <div class="font-medium">{{ $pedido->cliente->ci_nit }}</div>
                            </div>
                        </div>
                        
                        @if($pedido->cliente->email)
                        <div class="flex items-center">
                            <i class="fas fa-envelope w-5 text-gray-500 mr-3"></i>
                            <div>
                                <div class="text-sm text-gray-500">Email</div>
                                <div class="font-medium">
                                    <a href="mailto:{{ $pedido->cliente->email }}" class="text-blue-600 hover:underline">
                                        {{ $pedido->cliente->email }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($pedido->cliente->telefono)
                        <div class="flex items-center">
                            <i class="fas fa-phone w-5 text-gray-500 mr-3"></i>
                            <div>
                                <div class="text-sm text-gray-500">Teléfono</div>
                                <div class="font-medium">
                                    <a href="tel:{{ $pedido->cliente->telefono }}" class="text-blue-600 hover:underline">
                                        {{ $pedido->cliente->telefono }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($pedido->cliente->direccion)
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt w-5 text-gray-500 mr-3 mt-1"></i>
                            <div>
                                <div class="text-sm text-gray-500">Dirección</div>
                                <div class="font-medium">{{ $pedido->cliente->direccion }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('pedidos.cliente-historial', $pedido->cliente->id) }}" 
                           class="text-boom-primary hover:text-boom-primary-dark font-medium text-sm">
                            <i class="fas fa-history mr-1"></i>
                            Ver historial de pedidos
                        </a>
                    </div>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="bg-boom-cream-100 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-boom-text-dark mb-3">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Resumen
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tiempo transcurrido:</span>
                            <span class="font-medium">{{ $pedido->created_at->diffForHumans(null, true) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Estado actual:</span>
                            <span class="font-medium">{{ $pedido->estado }}</span>
                        </div>
                        @if($pedido->total)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Valor:</span>
                            <span class="font-medium text-boom-primary">{{ $pedido->total_formateado }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para asignar operario -->
    @if(Auth::user()->rol && Auth::user()->rol->nombre === 'Administrador' && $pedido->puedeSerAsignado())
    <div id="modalAsignar" class="fixed inset-0 bg-black bg-opacity-30 hidden z-50" onclick="cerrarModalAsignar()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
                <div class="bg-boom-primary text-white p-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>
                        Asignar Operario al Pedido #{{ $pedido->id_pedido }}
                    </h3>
                    <button onclick="cerrarModalAsignar()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form action="{{ route('pedidos.asignar', $pedido->id_pedido) }}" method="POST" class="p-4">
                    @csrf
                    <div class="mb-4">
                        <label for="id_operario" class="block text-sm font-medium text-gray-700 mb-2">
                            Seleccionar Operario:
                        </label>
                        <select name="id_operario" id="id_operario" class="form-select block w-full rounded-md shadow-sm" required>
                            <option value="">Seleccione un operario...</option>
                            @foreach(App\Models\User::whereHas('rol', function($q) { $q->where('nombre', 'Empleado'); })->get() as $operario)
                                <option value="{{ $operario->id_usuario }}">{{ $operario->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-4">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            Al asignar el operario, el estado del pedido cambiará automáticamente a "Asignado".
                        </p>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="cerrarModalAsignar()" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="bg-boom-primary hover:bg-boom-primary-dark text-white px-4 py-2 rounded">
                            Asignar Operario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        function mostrarModalAsignar() {
            document.getElementById('modalAsignar').classList.remove('hidden');
        }
        
        function cerrarModalAsignar() {
            document.getElementById('modalAsignar').classList.add('hidden');
        }
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalAsignar();
            }
        });
    </script>
    @endpush
</x-app-layout>