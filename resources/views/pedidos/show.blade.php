@extends('layouts.app')

@section('content')
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
                
                     @if(Auth::user()->id_rol == 1)
                     <a href="{{ route('pedidos.historial-avances', $pedido->id_pedido) }}" 
                   class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                    <i class="fas fa-tasks mr-1"></i>
                    Avances
                </a>
                     @endif

                     @if(in_array(Auth::user()->id_rol, [1, 2]))
                     <a href="{{ route('pedidos.historial-observaciones-calidad', $pedido->id_pedido) }}" 
                         class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                    <i class="fas fa-clipboard-check mr-1"></i>
                    Calidad
                </a>
                     @endif
                
                <!-- Botón Ver Detalles -->
                <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-eye mr-2"></i>
                    Ver Detalles
                </a>
                
                <!-- Botón Más Opciones -->
                @if($pedido->puedeReprogramarEntrega() || $pedido->estado == 'Terminado' || $pedido->puedeSerAsignado() || $pedido->puedeSerCancelado())
                <div class="relative inline-block text-left">
                    <button onclick="toggleHeaderDropdown()" 
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-ellipsis-h mr-2"></i>
                        Más opciones
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    
                    <div id="headerDropdownMenu" class="hidden absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            @if($pedido->puedeReprogramarEntrega() && (Auth::user()->id_rol == 1 || (Auth::user()->id_rol == 3 && $pedido->id_cliente == Auth::user()->id_usuario)))
                                <button onclick="mostrarModalReprogramar(); toggleHeaderDropdown();" 
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-900 flex items-center">
                                    <i class="fas fa-calendar-alt mr-3 text-blue-500"></i>
                                    Reprogramar Entrega
                                </button>
                                <a href="{{ route('pedidos.historial-reprogramaciones', $pedido->id_pedido) }}" 
                                   class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-900 flex items-center">
                                    <i class="fas fa-history mr-3 text-purple-500"></i>
                                    Ver historial de reprogramaciones
                                </a>
                            @endif
                            
                            @if($pedido->estado == 'Terminado' && (Auth::user()->id_rol == 1 || (Auth::user()->id_rol == 3 && $pedido->id_cliente == Auth::user()->id_usuario)))
                                <button onclick="mostrarModalConfirmarRecepcion(); toggleHeaderDropdown();" 
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-900 flex items-center">
                                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                    Confirmar Recepción
                                </button>
                            @endif
                            
                            @if(Auth::user()->rol && Auth::user()->rol->nombre === 'Administrador' && $pedido->puedeSerAsignado())
                                <button onclick="mostrarModalAsignar(); toggleHeaderDropdown();" 
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-900 flex items-center">
                                    <i class="fas fa-user-plus mr-3 text-purple-500"></i>
                                    Asignar Operario
                                </button>
                            @endif
                            
                            @if($pedido->puedeSerCancelado())
                                <form action="{{ route('pedidos.destroy', $pedido->id_pedido) }}" method="POST" class="inline w-full" 
                                      onsubmit="return confirm('¿Está seguro de que desea cancelar este pedido?\n\nEsta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-900 flex items-center">
                                        <i class="fas fa-times mr-3 text-red-500"></i>
                                        Cancelar Pedido
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <a href="{{ route('pedidos.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha estimada de entrega</label>
                            <div class="text-boom-text-dark">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                {{ $pedido->fecha_entrega_programada ? \Carbon\Carbon::parse($pedido->fecha_entrega_programada)->format('d/m/Y') : 'Sin fecha programada' }}
                            </div>
                        </div>

                        @if($pedido->fecha_reprogramacion)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Última reprogramación</label>
                            <div class="text-boom-text-dark">
                                <i class="fas fa-history mr-1"></i>
                                {{ $pedido->fecha_reprogramacion ? \Carbon\Carbon::parse($pedido->fecha_reprogramacion)->format('d/m/Y H:i') : '-' }}
                                <span class="text-sm text-gray-500 ml-2">por {{ $pedido->reprogramadoPor->nombre ?? 'Sistema' }}</span>
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
                                <p class="font-bold text-black">
                                    Bs. {{ number_format($prenda->pivot->precio_unitario * ($prenda->pivot->cantidad / 12), 2) }}
                                </p>
                                <p class="text-sm text-boom-text-medium">
                                    Bs. {{ number_format($prenda->pivot->precio_unitario, 2) }} c/docena
                                </p>
                            </div>
                            <div class="mt-3">
                                @if(Auth::user()->id_rol == 1)
                                    <a href="{{ route('pedidos.devoluciones.create', $pedido->id_pedido) }}?prenda_id={{ $prenda->id }}" class="inline-block mt-2 bg-red-500 hover:bg-red-600 text-black px-3 py-1 rounded text-sm">Registrar Devolución</a>
                                @endif
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
                                    <p><strong>Total devuelto:</strong> {{ $pedido->devoluciones->sum('cantidad') }} unidades</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-black">
                                    {{ $pedido->total_formateado }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Acciones Disponibles -->
                @if($pedido->puedeSerEditado() || $pedido->puedeSerCancelado() || $pedido->puedeSerAsignado() || $pedido->puedeReprogramarEntrega() || in_array($pedido->estado, ['Asignado', 'En producción', 'Terminado']))
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-boom-text-dark mb-4">
                        <i class="fas fa-cogs mr-2"></i>
                        Acciones Disponibles
                    </h2>
                    
                    <div class="flex flex-wrap gap-3">
                        <!-- Botones Principales -->
                        @if($pedido->puedeSerEditado())
                            <a href="{{ route('pedidos.edit', $pedido->id_pedido) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-edit mr-2"></i>
                                Editar
                            </a>
                        @endif
                        
                        @if(Auth::user()->id_rol == 1)
                            <a href="{{ route('pedidos.historial-avances', $pedido->id_pedido) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-history mr-2"></i>
                                Historial
                            </a>
                        @endif
                        
                        @if(in_array($pedido->estado, ['Asignado', 'En producción']) && Auth::user()->id_rol == 1)
                            <button onclick="mostrarModalAvance()" 
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-400 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white font-semibold rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-tasks mr-2"></i>
                                Avances
                            </button>
                        @endif
                        
                        @if(in_array($pedido->estado, ['En producción', 'Terminado']) && in_array(Auth::user()->id_rol, [1, 2]))
                            <button onclick="mostrarModalCalidad()" 
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-400 to-green-500 hover:from-emerald-500 hover:to-green-600 text-white font-semibold rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-clipboard-check mr-2"></i>
                                Calidad
                            </button>
                        @endif
                        
                        @if(in_array(Auth::user()->id_rol, [1, 2]))
                            <button onclick="mostrarModalCambiarEstado()" 
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                Cambiar Estado
                            </button>
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

    @push('styles')
    <style>
        /* Estilos para el slider personalizado */
        .slider-thumb::-webkit-slider-thumb {
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #1f2937;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .slider-thumb::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #1f2937;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .slider-thumb::-webkit-slider-track {
            height: 8px;
            border-radius: 4px;
            background: #e5e7eb;
        }
        
        .slider-thumb::-moz-range-track {
            height: 8px;
            border-radius: 4px;
            background: #e5e7eb;
            border: none;
        }
    </style>
    @endpush

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
                cerrarModalReprogramar();
                cerrarModalCambiarEstado();
                cerrarModalAvance();
                cerrarModalCalidad();
                cerrarModalConfirmarRecepcion();
            }
        });

        // ========== MODAL REPROGRAMAR ENTREGA ==========
        function mostrarModalReprogramar() {
            document.getElementById('modalReprogramar').classList.remove('hidden');
            // Establecer fecha mínima como mañana
            const fechaInput = document.getElementById('nueva_fecha_entrega_modal');
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            fechaInput.min = tomorrow.toISOString().split('T')[0];
        }

        function cerrarModalReprogramar(event) {
            if (event) event.preventDefault();
            document.getElementById('modalReprogramar').classList.add('hidden');
            document.getElementById('formReprogramar').reset();
        }

        // ========== MODAL CAMBIAR ESTADO ==========
        function mostrarModalCambiarEstado() {
            document.getElementById('modalCambiarEstado').classList.remove('hidden');
        }

        function cerrarModalCambiarEstado(event) {
            if (event) event.preventDefault();
            document.getElementById('modalCambiarEstado').classList.add('hidden');
            document.getElementById('formCambiarEstado').reset();
        }

        // ========== MODAL REGISTRAR AVANCE ==========
        function mostrarModalAvance() {
            document.getElementById('modalAvance').classList.remove('hidden');
        }

        function cerrarModalAvance(event) {
            if (event) event.preventDefault();
            document.getElementById('modalAvance').classList.add('hidden');
            document.getElementById('formAvance').reset();
        }

        // ========== MODAL OBSERVACIÓN CALIDAD ==========
        function mostrarModalCalidad() {
            document.getElementById('modalCalidad').classList.remove('hidden');
        }

        function cerrarModalCalidad(event) {
            if (event) event.preventDefault();
            document.getElementById('modalCalidad').classList.add('hidden');
            document.getElementById('formCalidad').reset();
        }

        // ========== MODAL CONFIRMAR RECEPCIÓN ==========
        function mostrarModalConfirmarRecepcion() {
            document.getElementById('modalConfirmarRecepcion').classList.remove('hidden');
        }

        function cerrarModalConfirmarRecepcion(event) {
            if (event) event.preventDefault();
            document.getElementById('modalConfirmarRecepcion').classList.add('hidden');
        }

        // ========== DROPDOWN MÁS OPCIONES HEADER ==========
        function toggleHeaderDropdown() {
            const dropdown = document.getElementById('headerDropdownMenu');
            dropdown.classList.toggle('hidden');
        }

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('headerDropdownMenu');
            const button = event.target.closest('button');
            
            if (!button || !button.onclick || button.onclick.toString().indexOf('toggleHeaderDropdown') === -1) {
                if (dropdown && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            }
        });
    </script>
    @endpush

    <!-- Modal Reprogramar Entrega -->
                    @if($pedido->puedeReprogramarEntrega() && (Auth::user()->id_rol == 1 || (Auth::user()->id_rol == 3 && $pedido->id_cliente == Auth::user()->id_usuario)))
    <div id="modalReprogramar" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Reprogramar Entrega</h3>
                    <button onclick="cerrarModalReprogramar(event)" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="formReprogramar" action="{{ route('pedidos.procesar-reprogramacion', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    
                    <!-- Fecha Actual -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Actual</label>
                        <input type="text" 
                               value="{{ $pedido->fecha_entrega_programada ? \Carbon\Carbon::parse($pedido->fecha_entrega_programada)->format('d/m/Y') : 'Sin fecha programada' }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" 
                               readonly>
                    </div>
                    
                    <!-- Nueva Fecha de Entrega -->
                    <div class="mb-4">
                        <label for="nueva_fecha_entrega_modal" class="block text-sm font-medium text-gray-700 mb-2">
                            Nueva Fecha de Entrega *
                        </label>
                        <input type="date" 
                               id="nueva_fecha_entrega_modal" 
                               name="nueva_fecha_entrega" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <!-- Motivo -->
                    <div class="mb-6">
                        <label for="motivo_reprogramacion_modal" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo *
                        </label>
                        <textarea id="motivo_reprogramacion_modal" 
                                  name="motivo_reprogramacion" 
                                  rows="3" 
                                  required
                                  placeholder="Describe el motivo de la reprogramación..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    
                    <!-- Botones -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="cerrarModalReprogramar(event)" 
                                class="bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white px-4 py-2 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </button>
                        <button type="submit" 
                                class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-2 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md font-semibold">
                            <i class="fas fa-calendar-check mr-2"></i>Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Cambiar Estado -->
    @if(in_array(Auth::user()->id_rol, [1, 2]))
    <div id="modalCambiarEstado" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Cambiar Estado</h3>
                    <button onclick="cerrarModalCambiarEstado(event)" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="formCambiarEstado" action="{{ route('pedidos.cambiar-estado-con-notificacion', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    
                    <!-- Estado Actual -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado Actual</label>
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($pedido->estado == 'Pendiente') bg-yellow-100 text-yellow-800
                                @elseif($pedido->estado == 'En proceso') bg-blue-100 text-blue-800
                                @elseif($pedido->estado == 'Asignado') bg-purple-100 text-purple-800
                                @elseif($pedido->estado == 'En producción') bg-orange-100 text-orange-800
                                @elseif($pedido->estado == 'Terminado') bg-green-100 text-green-800
                                @elseif($pedido->estado == 'Entregado') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $pedido->estado }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Nuevo Estado -->
                    <div class="mb-4">
                        <label for="nuevo_estado_modal" class="block text-sm font-medium text-gray-700 mb-2">
                            Nuevo Estado *
                        </label>
                        <select id="nuevo_estado_modal" name="nuevo_estado" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccionar nuevo estado...</option>
                            @php
                                $estadosDisponibles = ['Pendiente', 'En proceso', 'Asignado', 'En producción', 'Terminado', 'Entregado', 'Cancelado'];
                            @endphp
                            @foreach($estadosDisponibles as $estado)
                                @if($estado !== $pedido->estado)
                                    <option value="{{ $estado }}">{{ $estado }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Observaciones -->
                    <div class="mb-4">
                        <label for="observaciones_modal" class="block text-sm font-medium text-gray-700 mb-2">
                            Observaciones (Opcional)
                        </label>
                        <textarea id="observaciones_modal" 
                                  name="observaciones" 
                                  rows="3" 
                                  placeholder="Observaciones sobre el cambio de estado..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <!-- Información de Email -->
                    <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-400 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    @if($pedido->cliente && $pedido->cliente->email)
                                        Se enviará notificación por email a <strong>{{ $pedido->cliente->email }}</strong>
                                    @else
                                        <span class="text-red-600">El cliente no tiene email registrado</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="cerrarModalCambiarEstado(event)" 
                                class="bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white px-4 py-2 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </button>
                        <button type="submit" 
                                class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-2 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md font-semibold">
                            <i class="fas fa-paper-plane mr-2"></i>Cambiar y Notificar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Registrar Avance de Producción -->
    @if(in_array($pedido->estado, ['Asignado', 'En producción']) && Auth::user()->id_rol == 1)
    <div id="modalAvance" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">CU20: Registrar Avance de Producción</h3>
                    <button onclick="cerrarModalAvance(event)" class="text-gray-400 hover:text-gray-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <form id="formAvance" action="{{ route('pedidos.procesar-avance', $pedido->id_pedido) }}" method="POST">
                        @csrf
                        
                        <!-- Artículo -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Artículo</label>
                            <select name="prenda_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                @foreach($pedido->prendas as $prenda)
                                    <option value="{{ $prenda->id }}">{{ $prenda->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Etapa -->
                        <div class="mb-6">
                            <label for="etapa_modal" class="block text-sm font-medium text-gray-700 mb-2">Etapa</label>
                            <select id="etapa_modal" name="etapa" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                <option value="">Seleccionar etapa...</option>
                                <option value="Corte">Corte</option>
                                <option value="Confección">Confección</option>
                                <option value="Acabado">Acabado</option>
                                <option value="Control de Calidad">Control de Calidad</option>
                            </select>
                        </div>
                        
                        <!-- Porcentaje de Avance -->
                        <div class="mb-6">
                            <label for="porcentaje_avance_modal" class="block text-sm font-medium text-gray-700 mb-3">
                                Porcentaje de Avance: <span id="porcentaje_display" class="font-semibold">0%</span>
                            </label>
                            <div class="relative">
                                <input type="range" 
                                       id="porcentaje_avance_modal" 
                                       name="porcentaje_avance" 
                                       min="0" max="100" value="0" 
                                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-thumb"
                                       oninput="document.getElementById('porcentaje_display').textContent = this.value + '%'">
                            </div>
                        </div>
                        
                        <!-- Notas -->
                        <div class="mb-8">
                            <label for="descripcion_modal" class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                            <textarea id="descripcion_modal" 
                                      name="descripcion" 
                                      rows="4" 
                                      required
                                      placeholder="Descripción del avance..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                        </div>
                        
                        <!-- Botones -->
                        <div class="flex justify-center space-x-4">
                            <button type="submit" 
                                    class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg font-medium transition-colors duration-200">
                                Confirmar
                            </button>
                            <button type="button" 
                                    onclick="cerrarModalAvance(event)" 
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-8 py-3 rounded-lg font-medium transition-colors duration-200">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Registrar Observación de Calidad -->
    @if(in_array($pedido->estado, ['En producción', 'Terminado']) && in_array(Auth::user()->id_rol, [1, 2]))
    <div id="modalCalidad" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">CU21: Registrar Observación de Calidad</h3>
                    <button onclick="cerrarModalCalidad(event)" class="text-gray-400 hover:text-gray-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <form id="formCalidad" action="{{ route('pedidos.procesar-observacion-calidad', $pedido->id_pedido) }}" method="POST">
                        @csrf
                        
                        <!-- Artículo -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Artículo</label>
                            <select name="prenda_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                @foreach($pedido->prendas as $prenda)
                                    <option value="{{ $prenda->id }}">{{ $prenda->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Nivel de Severidad -->
                        <div class="mb-6">
                            <label for="prioridad_modal" class="block text-sm font-medium text-gray-700 mb-2">Nivel de Severidad</label>
                            <select id="prioridad_modal" name="prioridad" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                <option value="">Seleccionar nivel...</option>
                                <option value="Baja">Leve</option>
                                <option value="Media">Media</option>
                                <option value="Alta">Alta</option>
                                <option value="Crítica">Crítica</option>
                            </select>
                        </div>
                        
                        <!-- Observación -->
                        <div class="mb-6">
                            <label for="descripcion_calidad_modal" class="block text-sm font-medium text-gray-700 mb-2">Observación</label>
                            <textarea id="descripcion_calidad_modal" 
                                      name="descripcion" 
                                      rows="4" 
                                      required
                                      placeholder="Describe la observación de calidad..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                        </div>
                        
                        <!-- Artículo aprobado -->
                        <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="articulo_aprobado" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">Artículo aprobado</span>
                            </label>
                        </div>
                        
                        <!-- Botones -->
                        <div class="flex justify-center space-x-4">
                            <button type="submit" 
                                    class="bg-red-700 hover:bg-red-800 text-white px-8 py-3 rounded-lg font-medium transition-colors duration-200">
                                Confirmar
                            </button>
                            <button type="button" 
                                    onclick="cerrarModalCalidad(event)" 
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-8 py-3 rounded-lg font-medium transition-colors duration-200">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Confirmar Recepción -->
    @if($pedido->estado == 'Terminado' && in_array(Auth::user()->id_rol, [1, 2]))
    <div id="modalConfirmarRecepcion" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Confirmar Recepción de Pedido</h3>
                    <button onclick="cerrarModalConfirmarRecepcion(event)" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-600 text-center">
                        Confirma que el cliente ha recibido el pedido y envía una notificación por WhatsApp.
                    </p>
                </div>
                
                <form action="{{ route('pedidos.confirmar-recepcion', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    
                    <!-- Botón de Confirmación -->
                    <div class="text-center">
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-6 py-3 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg font-semibold">
                            <i class="fas fa-check-circle mr-2"></i>Confirmar Recepción
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection