@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <h1 class="text-2xl sm:text-3xl font-bold text-boom-text-dark">Gestión de Clientes</h1>
            @if(Auth::user()->id_rol == 1)
                <div class="flex gap-2">
                <a href="{{ route('clientes.create') }}" class="bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded text-center sm:text-left">
                    <i class="fas fa-plus mr-2"></i>
                    Nuevo Cliente
                </a>
                @if (app()->bound('dompdf.wrapper'))
                    @php
                        $delimiter = config('exports.csv_delimiter', ';');
                        $pdfRoute = (config('exports.noauth_enabled', false) === true && app()->environment('local')) ? route('debug.clientes.export.noauth') : route('clientes.exportar-pdf');
                        $csvRoute = (config('exports.noauth_enabled', false) === true && app()->environment('local')) ? route('debug.clientes.export.noauth') . '?format=csv&delimiter=' . urlencode($delimiter) : route('clientes.exportar-pdf', ['format' => 'csv', 'delimiter' => $delimiter]);
                        $jsonRoute = (config('exports.noauth_enabled', false) === true && app()->environment('local')) ? route('debug.clientes.export.noauth') . '?format=json' : route('clientes.exportar-pdf', ['format' => 'json']);
                    @endphp
                    <a href="{{ $pdfRoute }}" download class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center sm:text-left">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Exportar PDF
                    </a>
                    <a href="{{ $csvRoute }}" download class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-center sm:text-left">
                        <i class="fas fa-file-csv mr-2"></i>
                        Exportar CSV
                    </a>
                    <a href="{{ $jsonRoute }}" download class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center sm:text-left">
                        <i class="fas fa-file-code mr-2"></i>
                        Exportar JSON
                    </a>
                @else
                    <button type="button" class="bg-gray-400 text-white font-bold py-2 px-4 rounded text-center sm:text-left cursor-not-allowed" title="Instala barryvdh/laravel-dompdf para permitir exportar a PDF">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Exportar PDF (requiere DomPDF)
                    </button>
                    <a href="{{ $csvRoute }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-center sm:text-left">
                        <i class="fas fa-file-csv mr-2"></i>
                        Exportar CSV
                    </a>
                @endif
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Formulario de búsqueda -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar por nombre, apellido o CI/NIT</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           class="form-input block w-full rounded-md shadow-sm"
                           placeholder="Ingrese nombre, apellido o CI/NIT...">
                </div>
                <div class="flex sm:items-end gap-2">
                    <button type="submit" class="bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded flex-1 sm:flex-none">
                        <i class="fas fa-search mr-2 sm:mr-1"></i>
                        <span class="sm:hidden">Buscar</span>
                        <span class="hidden sm:inline">Buscar</span>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('clientes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex-1 sm:flex-none text-center">
                            <i class="fas fa-times mr-2 sm:mr-1"></i>
                            <span class="sm:hidden">Limpiar</span>
                            <span class="hidden sm:inline">Limpiar</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-boom-cream-100 rounded-xl shadow">
            <!-- Vista de tabla para pantallas grandes -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-boom-text-medium">
                        <tr>
                            <th class="p-3">Num</th>
                            <th class="p-3">Nombre Completo</th>
                            <th class="p-3">CI/NIT</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Teléfono</th>
                            <th class="p-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-boom-cream-200">
                        @forelse ($clientes as $cliente)
                        <tr class="text-boom-text-dark">
                            <td class="p-3">{{ ($clientes->currentPage() - 1) * $clientes->perPage() + $loop->iteration }}</td>
                            <td class="p-3 font-bold">{{ $cliente->nombre }} {{ $cliente->apellido }}</td>
                            <td class="p-3">{{ $cliente->ci_nit }}</td>
                            <td class="p-3">{{ $cliente->email ?? 'N/A' }}</td>
                            <td class="p-3">{{ $cliente->telefono ?? 'N/A' }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-2">
                                    <!-- Botón para crear pedido - disponible para todos -->
                                    <a href="{{ route('pedidos.create', ['cliente' => $cliente->id]) }}" 
                                       class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200"
                                       title="Crear pedido para este cliente">
                                        <i class="fas fa-shopping-bag mr-1"></i>
                                        Nuevo Pedido
                                    </a>
                                    
                                    <!-- Botón para ver historial de pedidos -->
                                    <a href="{{ route('pedidos.cliente-historial', $cliente->id) }}" 
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200"
                                       title="Ver historial de pedidos">
                                        <i class="fas fa-history mr-1"></i>
                                        Historial
                                    </a>
                                    
                                    @if(Auth::user()->id_rol == 1)
                                        <!-- Botones de administrador -->
                                        <a href="{{ route('clientes.edit', $cliente) }}" 
                                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200"
                                           title="Editar cliente">
                                            <i class="fas fa-edit mr-1"></i>
                                            Editar
                                        </a>
                                        <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('¿Está seguro de que desea eliminar este cliente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200"
                                                    title="Eliminar cliente">
                                                <i class="fas fa-trash mr-1"></i>
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-3 text-center text-gray-500">No hay clientes registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vista de tarjetas para móviles y tablets -->
            <div class="lg:hidden p-4 space-y-4">
                @forelse ($clientes as $cliente)
                <div class="bg-white rounded-lg p-4 shadow-sm border border-boom-cream-200">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="font-bold text-boom-text-dark text-lg">{{ $cliente->nombre }} {{ $cliente->apellido }}</h3>
                            <p class="text-sm text-boom-text-medium">Cliente #{{ ($clientes->currentPage() - 1) * $clientes->perPage() + $loop->iteration }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <div>
                            <span class="text-xs font-medium text-boom-text-medium uppercase tracking-wide">CI/NIT</span>
                            <p class="text-sm text-boom-text-dark">{{ $cliente->ci_nit }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-boom-text-medium uppercase tracking-wide">Email</span>
                            <p class="text-sm text-boom-text-dark">{{ $cliente->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-boom-text-medium uppercase tracking-wide">Teléfono</span>
                            <p class="text-sm text-boom-text-dark">{{ $cliente->telefono ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <!-- Botón para crear pedido - disponible para todos -->
                        <a href="{{ route('pedidos.create', ['cliente' => $cliente->id]) }}" 
                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm font-medium transition-colors duration-200 flex items-center"
                           title="Crear pedido para este cliente">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Nuevo Pedido
                        </a>
                        
                        <!-- Botón para ver historial de pedidos -->
                        <a href="{{ route('pedidos.cliente-historial', $cliente->id) }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm font-medium transition-colors duration-200 flex items-center"
                           title="Ver historial de pedidos">
                            <i class="fas fa-history mr-2"></i>
                            Historial
                        </a>
                        
                        @if(Auth::user()->id_rol == 1)
                            <!-- Botones de administrador -->
                            <a href="{{ route('clientes.edit', $cliente) }}" 
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded text-sm font-medium transition-colors duration-200 flex items-center"
                               title="Editar cliente">
                                <i class="fas fa-edit mr-2"></i>
                                Editar
                            </a>
                            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline" 
                                  onsubmit="return confirm('¿Está seguro de que desea eliminar este cliente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm font-medium transition-colors duration-200 flex items-center"
                                        title="Eliminar cliente">
                                    <i class="fas fa-trash mr-2"></i>
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-gray-500">No hay clientes registrados</p>
                </div>
                @endforelse
            </div>

            <!-- Paginación -->
            @if($clientes->hasPages())
                <div class="mt-4">
                    {{ $clientes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
