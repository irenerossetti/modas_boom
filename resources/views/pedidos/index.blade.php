@extends('layouts.app')

@section('content')
    <div class="p-2 sm:p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 lg:mb-6 space-y-3 sm:space-y-0">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-shopping-bag mr-2"></i>
                <span class="hidden sm:inline">Gestión de Pedidos</span>
                <span class="sm:hidden">Pedidos</span>
            </h1>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 lg:space-x-3">
                @if(Auth::user()->id_rol == 1 || Auth::user()->id_rol == 2)
                    <a href="{{ route('pedidos.empleado-crear') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-black font-bold py-2 px-3 sm:py-3 sm:px-4 lg:px-6 rounded-lg shadow-lg transition-all duration-300 text-sm sm:text-base text-center">
                        <i class="fas fa-plus mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Crear Nuevo Pedido</span>
                        <span class="sm:hidden">Nuevo Pedido</span>
                    </a>
                @endif
                <a href="{{ route('pedidos.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-3 sm:py-3 sm:px-4 lg:px-6 rounded-lg shadow-md transition-all duration-300 text-sm sm:text-base text-center">
                    <i class="fas fa-edit mr-1 sm:mr-2"></i>
                    <span class="hidden sm:inline">Pedido Manual</span>
                    <span class="sm:hidden">Manual</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulario de filtros -->
        <div class="bg-white p-3 sm:p-4 rounded-lg shadow mb-4 lg:mb-6">
            <form method="GET" action="{{ route('pedidos.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                <div>
                    <label for="busqueda" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="busqueda" id="busqueda" 
                           value="{{ $filtros['busqueda'] ?? '' }}"
                           placeholder="Número de pedido o cliente..."
                           class="form-input block w-full rounded-md shadow-sm">
                </div>
                
                @if(Auth::user()->id_rol == 1)
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" id="estado" class="form-select block w-full rounded-md shadow-sm">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $valor => $etiqueta)
                            <option value="{{ $valor }}" 
                                    {{ ($filtros['estado'] ?? '') == $valor ? 'selected' : '' }}>
                                {{ $etiqueta }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div>
                    <label for="id_cliente" class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <select name="id_cliente" id="id_cliente" class="form-select block w-full rounded-md shadow-sm">
                        <option value="">Todos los clientes</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" 
                                    {{ ($filtros['id_cliente'] ?? '') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }} {{ $cliente->apellido }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" 
                           value="{{ $filtros['fecha_desde'] ?? '' }}"
                           class="form-input block w-full rounded-md shadow-sm">
                </div>
                
                <div>
                    <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" 
                           value="{{ $filtros['fecha_hasta'] ?? '' }}"
                           class="form-input block w-full rounded-md shadow-sm">
                </div>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-2 sm:col-span-2 lg:col-span-5">
                    <button type="submit" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-bold py-2 px-4 sm:px-6 rounded-lg shadow-md transition-all duration-300 text-sm sm:text-base">
                        <i class="fas fa-search mr-1 sm:mr-2"></i>
                        Filtrar
                    </button>
                    @if(!empty($filtros))
                        <a href="{{ route('pedidos.index') }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 sm:px-6 rounded-lg shadow-md transition-all duration-300 text-sm sm:text-base text-center">
                            <i class="fas fa-times mr-1 sm:mr-2"></i>
                            Limpiar
                        </a>
                    @endif
                    @if(Auth::user()->rol && Auth::user()->rol->nombre === 'Administrador')
                        <a href="{{ route('pedidos.por-operario') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 sm:px-6 rounded-lg shadow-md transition-all duration-300 text-sm sm:text-base text-center">
                            <i class="fas fa-users mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Por Operario</span>
                            <span class="sm:hidden">Operarios</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-2 sm:gap-4 mb-4 lg:mb-6">
            <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-2 sm:p-4">
                <div class="flex items-center">
                    <i class="fas fa-clock text-yellow-600 text-lg sm:text-2xl mr-2 sm:mr-3"></i>
                    <div>
                        <p class="text-xs sm:text-sm text-yellow-800">En Proceso</p>
                        <p class="text-lg sm:text-xl font-bold text-yellow-900">{{ $pedidos->where('estado', 'En proceso')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-blue-100 border border-blue-300 rounded-lg p-2 sm:p-4">
                <div class="flex items-center">
                    <i class="fas fa-user-check text-blue-600 text-lg sm:text-2xl mr-2 sm:mr-3"></i>
                    <div>
                        <p class="text-xs sm:text-sm text-blue-800">Asignados</p>
                        <p class="text-lg sm:text-xl font-bold text-blue-900">{{ $pedidos->where('estado', 'Asignado')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-100 border border-purple-300 rounded-lg p-2 sm:p-4">
                <div class="flex items-center">
                    <i class="fas fa-cogs text-purple-600 text-lg sm:text-2xl mr-2 sm:mr-3"></i>
                    <div>
                        <p class="text-xs sm:text-sm text-purple-800">En Producción</p>
                        <p class="text-lg sm:text-xl font-bold text-purple-900">{{ $pedidos->where('estado', 'En producción')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-green-100 border border-green-300 rounded-lg p-2 sm:p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-lg sm:text-2xl mr-2 sm:mr-3"></i>
                    <div>
                        <p class="text-xs sm:text-sm text-green-800">Completados</p>
                        <p class="text-lg sm:text-xl font-bold text-green-900">{{ $pedidos->whereIn('estado', ['Terminado', 'Entregado'])->count() }}</p>
                    </div>
                </div>
            </div>
            @php
                $pedidosPagados = 0;
                $pedidosPendientes = 0;
                foreach($pedidos as $pedido) {
                    $totalPagado = $pedido->pagos->where('anulado', false)->sum('monto');
                    $totalPedido = $pedido->total ?? 0;
                    if ($totalPagado >= $totalPedido && $totalPedido > 0) {
                        $pedidosPagados++;
                    } else {
                        $pedidosPendientes++;
                    }
                }
            @endphp
            <div class="bg-emerald-100 border border-emerald-300 rounded-lg p-2 sm:p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-double text-emerald-600 text-lg sm:text-2xl mr-2 sm:mr-3"></i>
                    <div>
                        <p class="text-xs sm:text-sm text-emerald-800">Pagados</p>
                        <p class="text-lg sm:text-xl font-bold text-emerald-900">{{ $pedidosPagados }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-red-100 border border-red-300 rounded-lg p-2 sm:p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-lg sm:text-2xl mr-2 sm:mr-3"></i>
                    <div>
                        <p class="text-xs sm:text-sm text-red-800">Sin Pagar</p>
                        <p class="text-lg sm:text-xl font-bold text-red-900">{{ $pedidosPendientes }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de pedidos -->
        <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-boom-text-dark">
                    Lista de Pedidos 
                    <span class="text-sm text-gray-600">({{ $pedidos->total() }} pedidos)</span>
                </h2>
                @if(Auth::user()->id_rol == 1 || Auth::user()->id_rol == 2)
                    <a href="{{ route('pedidos.empleado-crear') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-black font-semibold py-2 px-6 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>
                        Nuevo Pedido
                    </a>
                @endif
            </div>
            
            <!-- Vista de tabla para desktop -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-boom-cream-400 text-boom-text-dark border-b-2 border-boom-cream-500">
                        <tr>
                            <th class="p-3 lg:p-4 font-semibold text-sm lg:text-base"># Pedido</th>
                            <th class="p-3 lg:p-4 font-semibold text-sm lg:text-base">Cliente</th>
                            <th class="p-3 lg:p-4 font-semibold text-sm lg:text-base">Estado</th>
                            <th class="p-3 lg:p-4 font-semibold text-sm lg:text-base">Total</th>
                            <th class="p-3 lg:p-4 font-semibold text-sm lg:text-base">Estado Pago</th>
                            <th class="p-3 lg:p-4 font-semibold text-sm lg:text-base">Fecha</th>
                            <th class="p-3 lg:p-4 font-semibold text-center text-sm lg:text-base">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-boom-cream-200">
                        @forelse ($pedidos as $pedido)
                        <tr class="text-boom-text-dark hover:bg-boom-cream-50 border-b border-boom-cream-200">
                            <td class="p-4">
                                <div class="font-bold text-boom-primary">
                                    #{{ $pedido->id_pedido }}
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-boom-primary rounded-full flex items-center justify-center text-white font-bold mr-3 shadow-sm">
                                        {{ strtoupper(substr($pedido->cliente->nombre, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-boom-text-dark">
                                            {{ $pedido->nombre_completo_cliente }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-id-card mr-1"></i>
                                            {{ $pedido->cliente->ci_nit }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $pedido->estado_color }}">
                                    <i class="{{ $pedido->estado_icono }} mr-1"></i>
                                    {{ $pedido->estado }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-boom-text-dark">
                                    {{ $pedido->total_formateado }}
                                </div>
                            </td>
                            <td class="p-4">
                                @php
                                    $totalPagado = $pedido->pagos->where('anulado', false)->sum('monto');
                                    $totalPedido = $pedido->total ?? 0;
                                    
                                    // Verificar si hay reembolso
                                    $reembolso = null;
                                    foreach($pedido->pagos->where('anulado', false) as $pago) {
                                        $reembolsoExistente = \App\Models\SolicitudReembolso::where('pago_id', $pago->id)->first();
                                        if ($reembolsoExistente) {
                                            $reembolso = $reembolsoExistente;
                                            break;
                                        }
                                    }
                                    
                                    if ($reembolso && $reembolso->estado !== 'rechazado') {
                                        if ($reembolso->estado === 'procesado') {
                                            $estadoPago = 'Reembolsado';
                                            $colorPago = 'bg-emerald-100 text-emerald-800';
                                            $icono = 'fas fa-money-bill-wave';
                                        } else {
                                            $estadoPago = 'Reembolso Pendiente';
                                            $colorPago = 'bg-orange-100 text-orange-800';
                                            $icono = 'fas fa-clock';
                                        }
                                    } else {
                                        // Si no hay reembolso o está rechazado, mostrar estado normal del pago
                                        // Si está rechazado, el pago debería haber sido restaurado (anulado=false)
                                        if ($totalPagado >= $totalPedido && $totalPedido > 0) {
                                            $estadoPago = 'Pagado';
                                            $colorPago = 'bg-green-100 text-green-800';
                                            $icono = 'fas fa-check-circle';
                                        } elseif ($totalPagado > 0) {
                                            $estadoPago = 'Parcial';
                                            $colorPago = 'bg-yellow-100 text-yellow-800';
                                            $icono = 'fas fa-clock';
                                        } else {
                                            $estadoPago = 'Sin pagar';
                                            $colorPago = 'bg-red-100 text-red-800';
                                            $icono = 'fas fa-times-circle';
                                        }
                                    }
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $colorPago }}">
                                    <i class="{{ $icono }} mr-1"></i>
                                    {{ $estadoPago }}
                                </span>
                                @if($totalPagado > 0 && !$reembolso)
                                    <div class="text-xs text-gray-500 mt-1">
                                        Bs. {{ number_format($totalPagado, 2) }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="text-sm">
                                    <div class="font-semibold text-boom-text-dark">
                                        {{ $pedido->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $pedido->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center space-x-1 flex-wrap">
                                    <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(Auth::user()->id_rol == 1 || Auth::user()->id_rol == 2)
                                        @php
                                            $totalPagadoBtn = $pedido->pagos->where('anulado', false)->sum('monto');
                                            $totalPedidoBtn = $pedido->total ?? 0;
                                            $estaPagado = $totalPagadoBtn >= $totalPedidoBtn && $totalPedidoBtn > 0;
                                            
                                            // Verificar si ya tiene reembolso
                                            $tieneReembolso = false;
                                            foreach($pedido->pagos->where('anulado', false) as $pago) {
                                                if (\App\Models\SolicitudReembolso::where('pago_id', $pago->id)->exists()) {
                                                    $tieneReembolso = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        
                                        @if($estaPagado && !$tieneReembolso)
                                            <a href="{{ route('pagos.reembolso', $pedido->id_pedido) }}" 
                                               class="bg-orange-500 hover:bg-orange-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                               title="Reembolsar">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        @elseif(!$estaPagado)
                                            <a href="{{ route('pagos.checkout', $pedido->id_pedido) }}" 
                                               class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                               title="Pagar">
                                                <i class="fas fa-dollar-sign"></i>
                                            </a>
                                        @endif
                                    @endif
                                    
                                    @if($pedido->puedeSerEditado())
                                        <a href="{{ route('pedidos.edit', $pedido->id_pedido) }}" 
                                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    
                                    @if(Auth::user()->rol && Auth::user()->rol->nombre === 'Administrador' && $pedido->puedeSerAsignado())
                                        <button onclick="mostrarModalAsignar({{ $pedido->id_pedido }})" 
                                                class="bg-indigo-500 hover:bg-indigo-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                                title="Asignar operario">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    @endif
                                    
                                    @if($pedido->puedeSerCancelado())
                                        <form action="{{ route('pedidos.destroy', $pedido->id_pedido) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('¿Está seguro de que desea cancelar este pedido?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                                    title="Cancelar pedido">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('pedidos.historial', $pedido->id_pedido) }}" 
                                       class="bg-purple-500 hover:bg-purple-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                       title="Ver historial">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="bg-boom-cream-50 rounded-lg p-8">
                                    <i class="fas fa-shopping-bag text-6xl text-boom-text-medium mb-4"></i>
                                    <h3 class="text-xl font-semibold text-boom-text-dark mb-2">No hay pedidos</h3>
                                    <p class="text-boom-text-medium mb-6">
                                        @if(!empty($filtros))
                                            No hay pedidos que coincidan con los filtros aplicados.
                                        @else
                                            Aún no hay pedidos registrados en el sistema.
                                        @endif
                                    </p>
                                    @if(Auth::user()->id_rol == 1 || Auth::user()->id_rol == 2)
                                        <a href="{{ route('pedidos.empleado-crear') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-300">
                                            <i class="fas fa-plus mr-2"></i>Crear Primer Pedido
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vista de tarjetas para móviles -->
            <div class="lg:hidden space-y-3">
                @forelse ($pedidos as $pedido)
                <div class="bg-white border border-boom-cream-200 rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-boom-primary rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                                {{ strtoupper(substr($pedido->cliente->nombre, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-bold text-boom-primary text-sm">
                                    #{{ $pedido->id_pedido }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $pedido->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end space-y-1">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $pedido->estado_color }}">
                                <i class="{{ $pedido->estado_icono }} mr-1"></i>
                                {{ $pedido->estado }}
                            </span>
                            @php
                                $totalPagado = $pedido->pagos->where('anulado', false)->sum('monto');
                                $totalPedido = $pedido->total ?? 0;
                                $estadoPago = 'Sin pagar';
                                $colorPago = 'bg-red-100 text-red-800';
                                
                                if ($totalPagado >= $totalPedido && $totalPedido > 0) {
                                    $estadoPago = 'Pagado';
                                    $colorPago = 'bg-green-100 text-green-800';
                                } elseif ($totalPagado > 0) {
                                    $estadoPago = 'Parcial';
                                    $colorPago = 'bg-yellow-100 text-yellow-800';
                                }
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $colorPago }}">
                                <i class="fas fa-credit-card mr-1"></i>
                                {{ $estadoPago }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="font-semibold text-boom-text-dark text-sm">
                            {{ $pedido->nombre_completo_cliente }}
                        </div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-id-card mr-1"></i>
                            {{ $pedido->cliente->ci_nit }}
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-semibold text-boom-text-dark">
                                {{ $pedido->total_formateado }}
                            </div>
                            @if($totalPagado > 0)
                                <div class="text-xs text-gray-500">
                                    Pagado: Bs. {{ number_format($totalPagado, 2) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex space-x-1 flex-wrap">
                            <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                               title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @if(Auth::user()->id_rol == 1 || Auth::user()->id_rol == 2)
                                @if($estaPagado && !$tieneReembolso)
                                    <a href="{{ route('pagos.reembolso', $pedido->id_pedido) }}" 
                                       class="bg-orange-500 hover:bg-orange-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                       title="Reembolsar">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                @elseif(!$estaPagado)
                                    <a href="{{ route('pagos.checkout', $pedido->id_pedido) }}" 
                                       class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                       title="Pagar">
                                        <i class="fas fa-dollar-sign"></i>
                                    </a>
                                @endif
                            @endif
                            
                            @if($pedido->puedeSerEditado())
                                <a href="{{ route('pedidos.edit', $pedido->id_pedido) }}" 
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            
                            <a href="{{ route('pedidos.historial', $pedido->id_pedido) }}" 
                               class="bg-purple-500 hover:bg-purple-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors duration-200 mb-1"
                               title="Ver historial">
                                <i class="fas fa-history"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-boom-cream-50 rounded-lg p-6 text-center">
                    <i class="fas fa-shopping-bag text-4xl text-boom-text-medium mb-3"></i>
                    <h3 class="text-lg font-semibold text-boom-text-dark mb-2">No hay pedidos</h3>
                    <p class="text-boom-text-medium text-sm mb-4">
                        @if(!empty($filtros))
                            No hay pedidos que coincidan con los filtros aplicados.
                        @else
                            Aún no hay pedidos registrados en el sistema.
                        @endif
                    </p>
                    @if(Auth::user()->id_rol == 1 || Auth::user()->id_rol == 2)
                        <a href="{{ route('pedidos.empleado-crear') }}" class="bg-boom-rose-dark hover:bg-boom-rose-light text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300 text-sm">
                            <i class="fas fa-plus mr-2"></i>Crear Primer Pedido
                        </a>
                    @endif
                </div>
                @endforelse
            </div>

            <!-- Paginación -->
            @if($pedidos->hasPages())
                <div class="mt-4">
                    {{ $pedidos->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para asignar operario -->
    @if(Auth::user()->rol && Auth::user()->rol->nombre === 'Administrador')
    <div id="modalAsignar" class="fixed inset-0 bg-black bg-opacity-30 hidden z-50" onclick="cerrarModalAsignar()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
                <div class="bg-boom-primary text-white p-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>
                        Asignar Operario
                    </h3>
                    <button onclick="cerrarModalAsignar()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="formAsignar" method="POST" class="p-4">
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
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="cerrarModalAsignar()" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="bg-boom-primary hover:bg-boom-primary-dark text-white px-4 py-2 rounded">
                            Asignar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif



    @push('scripts')
    <script>
        function mostrarModalAsignar(pedidoId) {
            document.getElementById('formAsignar').action = '/pedidos/' + pedidoId + '/asignar';
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
@endsection
