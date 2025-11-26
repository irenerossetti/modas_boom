@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-history mr-2"></i>
                Bitácora del Sistema
            </h1>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('info') }}
            </div>
        @endif

        <!-- Formulario de filtros -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form method="GET" action="{{ route('bitacora.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" 
                           value="{{ $filtros['fecha_desde'] ?? '' }}"
                           class="form-input block w-full rounded-md shadow-sm">
                </div>
                
                <div>
                    <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" 
                           value="{{ $filtros['fecha_hasta'] ?? '' }}"
                           class="form-input block w-full rounded-md shadow-sm">
                </div>
                
                <div>
                    <label for="id_usuario" class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                    <select name="id_usuario" id="id_usuario" class="form-select block w-full rounded-md shadow-sm">
                        <option value="">Todos los usuarios</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id_usuario }}" 
                                    {{ ($filtros['id_usuario'] ?? '') == $usuario->id_usuario ? 'selected' : '' }}>
                                {{ $usuario->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="accion" class="block text-sm font-medium text-gray-700 mb-1">Acción</label>
                    <select name="accion" id="accion" class="form-select block w-full rounded-md shadow-sm">
                        <option value="">Todas las acciones</option>
                        @foreach($acciones as $valor => $etiqueta)
                            <option value="{{ $valor }}" 
                                    {{ ($filtros['accion'] ?? '') == $valor ? 'selected' : '' }}>
                                {{ $etiqueta }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="modulo" class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
                    <select name="modulo" id="modulo" class="form-select block w-full rounded-md shadow-sm">
                        <option value="">Todos los módulos</option>
                        @foreach($modulos as $valor => $etiqueta)
                            <option value="{{ $valor }}" 
                                    {{ ($filtros['modulo'] ?? '') == $valor ? 'selected' : '' }}>
                                {{ $etiqueta }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="busqueda" class="block text-sm font-medium text-gray-700 mb-1">Búsqueda</label>
                    <input type="text" name="busqueda" id="busqueda" 
                           value="{{ $filtros['busqueda'] ?? '' }}"
                           placeholder="Buscar en descripción..."
                           class="form-input block w-full rounded-md shadow-sm">
                </div>
                
                <div class="flex items-end gap-2 md:col-span-2">
                    <button type="submit" class="bg-boom-primary hover:bg-boom-primary-dark text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-search mr-1"></i>
                        Filtrar
                    </button>
                    @if(!empty($filtros))
                        <a href="{{ route('bitacora.limpiar-filtros') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla de registros -->
        <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-boom-text-dark">
                    Registros de Actividad 
                    <span class="text-sm text-gray-600">({{ $registros->total() }} registros)</span>
                </h2>
                
                <!-- Botón de exportar (funcionalidad futura) -->
                @php
                    $bitacoraRoute = (config('exports.noauth_enabled', false) === true && app()->environment('local')) ? route('debug.bitacora.export.noauth') : route('bitacora.exportar');
                @endphp
                <form action="{{ $bitacoraRoute }}" method="POST" class="inline">
                    @csrf
                    @foreach($filtros as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    @if(config('exports.noauth_enabled', false) === true && app()->environment('local'))
                        <input type="hidden" name="delimiter" value="{{ config('exports.csv_delimiter', ';') }}">
                    @endif
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                        <i class="fas fa-download mr-1"></i>
                        Exportar
                    </button>
                </form>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-boom-cream-200 text-boom-text-dark">
                        <tr>
                            <th class="p-4 font-semibold">Fecha y Hora</th>
                            <th class="p-4 font-semibold">Usuario</th>
                            <th class="p-4 font-semibold">Acción</th>
                            <th class="p-4 font-semibold">Módulo</th>
                            <th class="p-4 font-semibold">Descripción</th>
                            <th class="p-4 font-semibold">IP</th>
                            <th class="p-4 font-semibold text-center">Detalles</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-boom-cream-200">
                        @forelse ($registros as $registro)
                        <tr class="text-boom-text-dark hover:bg-boom-cream-50 border-b border-boom-cream-200">
                            <td class="p-4">
                                <div class="text-sm">
                                    <div class="font-semibold text-boom-text-dark">
                                        {{ $registro->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $registro->created_at->format('H:i:s') }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ $registro->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-boom-primary rounded-full flex items-center justify-center text-white font-bold mr-3 shadow-sm">
                                        {{ $registro->avatar_usuario }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-boom-text-dark">
                                            {{ $registro->nombre_usuario }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-user-tag mr-1"></i>
                                            {{ $registro->rol_usuario }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 text-sm font-medium rounded-full
                                    @if($registro->accion == 'LOGIN') bg-green-100 text-green-800
                                    @elseif($registro->accion == 'LOGOUT') bg-yellow-100 text-yellow-800
                                    @elseif($registro->accion == 'CREATE') bg-blue-100 text-blue-800
                                    @elseif($registro->accion == 'UPDATE') bg-orange-100 text-orange-800
                                    @elseif($registro->accion == 'DELETE') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($registro->accion == 'LOGIN')
                                        <i class="fas fa-sign-in-alt mr-1"></i>
                                    @elseif($registro->accion == 'LOGOUT')
                                        <i class="fas fa-sign-out-alt mr-1"></i>
                                    @elseif($registro->accion == 'CREATE')
                                        <i class="fas fa-plus mr-1"></i>
                                    @elseif($registro->accion == 'UPDATE')
                                        <i class="fas fa-edit mr-1"></i>
                                    @elseif($registro->accion == 'DELETE')
                                        <i class="fas fa-trash mr-1"></i>
                                    @else
                                        <i class="fas fa-eye mr-1"></i>
                                    @endif
                                    {{ $acciones[$registro->accion] ?? $registro->accion }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded-lg font-medium">
                                    {{ $modulos[$registro->modulo] ?? $registro->modulo }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="max-w-sm" title="{{ $registro->descripcion }}">
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        {{ Str::limit($registro->descripcion, 60) }}
                                    </p>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-globe mr-1"></i>
                                    {{ $registro->ip_address ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                @if($registro->datos_anteriores || $registro->datos_nuevos)
                                    <button type="button" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200"
                                            onclick="mostrarDetalles({{ $registro->id_bitacora }})">
                                        <i class="fas fa-eye mr-1"></i>
                                        Ver Detalles
                                    </button>
                                @else
                                    <span class="text-gray-400 text-sm">Sin detalles</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-2"></i><br>
                                No hay registros que coincidan con los filtros aplicados
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($registros->hasPages())
                <div class="mt-4">
                    {{ $registros->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para mostrar detalles -->
    <div id="modalDetalles" class="fixed inset-0 bg-black bg-opacity-30 hidden z-50" onclick="cerrarModal()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
                <!-- Header del modal -->
                <div class="bg-boom-primary text-white p-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Detalles del Registro
                    </h3>
                    <button onclick="cerrarModal()" class="text-white hover:text-gray-200 hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <!-- Contenido del modal -->
                <div id="contenidoModal" class="p-4 overflow-y-auto max-h-[calc(80vh-60px)] text-sm">
                    <!-- Contenido dinámico -->
                </div>
                
                <!-- Footer del modal -->
                <div class="bg-gray-50 px-4 py-3 border-t flex justify-end">
                    <button onclick="cerrarModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function mostrarDetalles(registroId) {
            // Buscar el registro en los datos
            const registros = @json($registros->items());
            const registro = registros.find(r => r.id_bitacora === registroId);
            
            if (!registro) return;
            
            let contenido = '<div class="space-y-4">';
            
            // Información del registro
            contenido += '<div class="bg-blue-50 p-4 rounded-lg border border-blue-200">';
            contenido += '<h4 class="font-semibold text-blue-800 mb-3 flex items-center">';
            contenido += '<i class="fas fa-info-circle mr-2"></i>Información General';
            contenido += '</h4>';
            contenido += '<div class="grid grid-cols-1 gap-3">';
            
            // Información compacta
            contenido += '<div class="bg-white p-3 rounded border">';
            contenido += '<div class="grid grid-cols-2 gap-3 text-xs">';
            contenido += '<div><i class="fas fa-calendar text-blue-600 mr-1"></i><strong>Fecha:</strong><br>' + formatearFecha(registro.created_at) + '</div>';
            contenido += '<div><i class="fas fa-user text-blue-600 mr-1"></i><strong>Usuario:</strong><br>' + (registro.usuario ? registro.usuario.nombre : 'Sistema') + '</div>';
            contenido += '<div><i class="fas fa-cog text-blue-600 mr-1"></i><strong>Acción:</strong><br>' + obtenerNombreAccion(registro.accion) + '</div>';
            contenido += '<div><i class="fas fa-globe text-blue-600 mr-1"></i><strong>IP:</strong><br>' + (registro.ip_address || 'N/A') + '</div>';
            contenido += '</div>';
            contenido += '</div>';
            
            contenido += '</div>';
            contenido += '</div>';
            
            if (registro.datos_anteriores) {
                contenido += '<div>';
                contenido += '<h4 class="font-semibold text-red-700 mb-2 flex items-center text-sm">';
                contenido += '<i class="fas fa-history mr-2"></i>Datos Anteriores';
                contenido += '</h4>';
                contenido += '<div class="bg-red-50 p-3 rounded border border-red-200">';
                contenido += formatearDatosCompactos(registro.datos_anteriores);
                contenido += '</div>';
                contenido += '</div>';
            }
            
            if (registro.datos_nuevos) {
                contenido += '<div>';
                contenido += '<h4 class="font-semibold text-green-700 mb-2 flex items-center text-sm">';
                contenido += '<i class="fas fa-check-circle mr-2"></i>Datos Nuevos';
                contenido += '</h4>';
                contenido += '<div class="bg-green-50 p-3 rounded border border-green-200">';
                contenido += formatearDatosCompactos(registro.datos_nuevos);
                contenido += '</div>';
                contenido += '</div>';
            }
            
            if (registro.user_agent) {
                contenido += '<div>';
                contenido += '<h4 class="font-semibold text-gray-700 mb-2 flex items-center text-sm">';
                contenido += '<i class="fas fa-desktop mr-2"></i>Navegador';
                contenido += '</h4>';
                contenido += '<div class="bg-gray-50 p-3 rounded border border-gray-200">';
                contenido += '<div class="text-xs text-gray-700 break-all font-mono">';
                contenido += registro.user_agent.substring(0, 100) + (registro.user_agent.length > 100 ? '...' : '');
                contenido += '</div>';
                contenido += '</div>';
                contenido += '</div>';
            }
            
            contenido += '</div>';
            
            document.getElementById('contenidoModal').innerHTML = contenido;
            abrirModal();
        }
        
        function formatearDatosCompactos(datos) {
            let html = '<div class="space-y-2">';
            
            for (const [key, value] of Object.entries(datos)) {
                let etiqueta = formatearEtiqueta(key);
                html += '<div class="flex justify-between items-center text-xs border-b border-gray-200 pb-1">';
                html += '<span class="font-medium text-gray-700">' + etiqueta + ':</span>';
                html += '<span class="text-gray-600 text-right ml-2">';
                
                if (typeof value === 'object' && value !== null) {
                    html += 'Ver objeto';
                } else if (typeof value === 'boolean') {
                    html += '<span class="px-1 py-0.5 rounded text-xs ' + (value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') + '">';
                    html += value ? 'Sí' : 'No';
                    html += '</span>';
                } else if (value === null || value === '') {
                    html += '<em class="text-gray-400">Sin datos</em>';
                } else if (key.includes('email')) {
                    html += '<a href="mailto:' + value + '" class="text-blue-600 hover:underline">' + value + '</a>';
                } else if (key.includes('telefono')) {
                    html += '<a href="tel:' + value + '" class="text-blue-600 hover:underline">' + value + '</a>';
                } else {
                    let valorMostrar = String(value);
                    if (valorMostrar.length > 30) {
                        valorMostrar = valorMostrar.substring(0, 30) + '...';
                    }
                    html += '<span class="font-medium">' + valorMostrar + '</span>';
                }
                
                html += '</span>';
                html += '</div>';
            }
            
            html += '</div>';
            return html;
        }
        
        function formatearEtiqueta(key) {
            const etiquetas = {
                'id': 'ID',
                'id_usuario': 'ID de Usuario',
                'id_rol': 'ID de Rol',
                'nombre': 'Nombre',
                'apellido': 'Apellido',
                'email': 'Correo Electrónico',
                'telefono': 'Teléfono',
                'direccion': 'Dirección',
                'ci_nit': 'CI/NIT',
                'password': 'Contraseña',
                'habilitado': 'Estado',
                'created_at': 'Fecha de Creación',
                'updated_at': 'Fecha de Actualización',
                'usuario_id': 'Usuario',
                'timestamp': 'Marca de Tiempo'
            };
            
            return etiquetas[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }
        
        function formatearObjeto(obj) {
            let html = '<div class="bg-gray-50 p-3 rounded border">';
            for (const [subKey, subValue] of Object.entries(obj)) {
                html += '<div class="flex justify-between py-1">';
                html += '<span class="text-sm text-gray-600">' + formatearEtiqueta(subKey) + ':</span>';
                html += '<span class="text-sm font-medium">' + (subValue || 'N/A') + '</span>';
                html += '</div>';
            }
            html += '</div>';
            return html;
        }
        
        function formatearFechaValor(fechaString) {
            try {
                const fecha = new Date(fechaString);
                const opciones = { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                return '<span class="text-blue-600 font-medium">' + fecha.toLocaleDateString('es-ES', opciones) + '</span>';
            } catch (e) {
                return '<span class="font-medium">' + fechaString + '</span>';
            }
        }
        
        function formatearFecha(fechaString) {
            const fecha = new Date(fechaString);
            const opciones = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            return fecha.toLocaleDateString('es-ES', opciones);
        }
        
        function obtenerNombreAccion(accion) {
            const acciones = {
                'LOGIN': 'Inicio de Sesión',
                'LOGOUT': 'Cierre de Sesión',
                'CREATE': 'Crear Registro',
                'UPDATE': 'Actualizar Registro',
                'DELETE': 'Eliminar Registro',
                'VIEW': 'Consultar Información'
            };
            return acciones[accion] || accion;
        }
        
        function obtenerNombreModulo(modulo) {
            const modulos = {
                'AUTH': 'Autenticación',
                'USUARIOS': 'Gestión de Usuarios',
                'CLIENTES': 'Gestión de Clientes',
                'ROLES': 'Gestión de Roles',
                'PEDIDOS': 'Gestión de Pedidos',
                'BITACORA': 'Bitácora del Sistema',
                'SISTEMA': 'Sistema General'
            };
            return modulos[modulo] || modulo;
        }
        
        function cerrarModal() {
            document.getElementById('modalDetalles').classList.add('hidden');
            document.body.style.overflow = 'auto'; // Restaurar scroll
        }
        
        function abrirModal() {
            document.getElementById('modalDetalles').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevenir scroll del fondo
        }
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModal();
            }
        });
    </script>
    @endpush
@endsection
