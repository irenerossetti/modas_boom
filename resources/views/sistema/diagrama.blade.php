@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-purple-600 to-blue-600 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold">🏗️ Diagrama de Arquitectura</h1>
                            <p class="text-purple-100 mt-1">Comunicación y flujos del sistema Modas Boom</p>
                        </div>
                        <a href="{{ route('sistema.index') }}" 
                           class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Panel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Arquitectura General -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-sitemap mr-2 text-blue-500"></i>Arquitectura General
                    </h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Frontend -->
                            <div class="text-center">
                                <div class="bg-blue-500 text-white p-4 rounded-lg mb-3">
                                    <i class="fas fa-desktop text-3xl mb-2"></i>
                                    <h3 class="font-bold">FRONTEND</h3>
                                    <p class="text-sm">Blade Views</p>
                                </div>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>• Vistas Blade</li>
                                    <li>• Tailwind CSS</li>
                                    <li>• JavaScript</li>
                                    <li>• Formularios</li>
                                </ul>
                            </div>

                            <!-- Backend -->
                            <div class="text-center">
                                <div class="bg-green-500 text-white p-4 rounded-lg mb-3">
                                    <i class="fas fa-server text-3xl mb-2"></i>
                                    <h3 class="font-bold">BACKEND</h3>
                                    <p class="text-sm">Laravel Framework</p>
                                </div>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>• Controladores</li>
                                    <li>• Middleware</li>
                                    <li>• Modelos Eloquent</li>
                                    <li>• Rutas</li>
                                </ul>
                            </div>

                            <!-- Database -->
                            <div class="text-center">
                                <div class="bg-purple-500 text-white p-4 rounded-lg mb-3">
                                    <i class="fas fa-database text-3xl mb-2"></i>
                                    <h3 class="font-bold">DATABASE</h3>
                                    <p class="text-sm">SQLite</p>
                                </div>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>• Tablas relacionales</li>
                                    <li>• Índices optimizados</li>
                                    <li>• Transacciones</li>
                                    <li>• Migraciones</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Flechas de comunicación -->
                        <div class="flex justify-center items-center mt-6 space-x-4">
                            <div class="text-2xl text-blue-500">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <span class="text-gray-600">Comunicación bidireccional</span>
                            <div class="text-2xl text-green-500">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controladores -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-cogs mr-2 text-green-500"></i>Controladores del Sistema
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- UserController -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-bold text-blue-900 mb-2">
                                <i class="fas fa-user mr-2"></i>UserController
                            </h3>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• index() - Listar usuarios</li>
                                <li>• store() - Crear usuario</li>
                                <li>• update() - Actualizar</li>
                                <li>• destroy() - Eliminar</li>
                            </ul>
                        </div>

                        <!-- PedidoController -->
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-bold text-green-900 mb-2">
                                <i class="fas fa-shopping-cart mr-2"></i>PedidoController
                            </h3>
                            <ul class="text-sm text-green-700 space-y-1">
                                <li>• clienteStore() - Crear pedido</li>
                                <li>• show() - Ver detalles</li>
                                <li>• destroy() - Cancelar</li>
                                <li>• misPedidos() - Historial</li>
                            </ul>
                        </div>

                        <!-- PrendaController -->
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-bold text-purple-900 mb-2">
                                <i class="fas fa-tshirt mr-2"></i>PrendaController
                            </h3>
                            <ul class="text-sm text-purple-700 space-y-1">
                                <li>• index() - Catálogo</li>
                                <li>• store() - Crear prenda</li>
                                <li>• update() - Actualizar</li>
                                <li>• destroy() - Eliminar</li>
                            </ul>
                        </div>

                        <!-- RolController -->
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h3 class="font-bold text-yellow-900 mb-2">
                                <i class="fas fa-user-tag mr-2"></i>RolController
                            </h3>
                            <ul class="text-sm text-yellow-700 space-y-1">
                                <li>• index() - Listar roles</li>
                                <li>• edit() - Editar (protegido)</li>
                                <li>• update() - Actualizar</li>
                                <li>• destroy() - Eliminar</li>
                            </ul>
                        </div>

                        <!-- ClienteController -->
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h3 class="font-bold text-red-900 mb-2">
                                <i class="fas fa-users mr-2"></i>ClienteController
                            </h3>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• index() - Listar clientes</li>
                                <li>• store() - Crear cliente</li>
                                <li>• update() - Actualizar</li>
                                <li>• destroy() - Eliminar</li>
                            </ul>
                        </div>

                        <!-- SistemaController -->
                        <div class="bg-indigo-50 p-4 rounded-lg">
                            <h3 class="font-bold text-indigo-900 mb-2">
                                <i class="fas fa-server mr-2"></i>SistemaController
                            </h3>
                            <ul class="text-sm text-indigo-700 space-y-1">
                                <li>• index() - Panel sistema</li>
                                <li>• diagrama() - Esta vista</li>
                                <li>• estadisticas() - API stats</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middleware de Seguridad -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-shield-alt mr-2 text-red-500"></i>Middleware de Seguridad
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-red-50 p-4 rounded-lg">
                                <h3 class="font-bold text-red-900 mb-2">CheckAdminRole</h3>
                                <p class="text-sm text-red-700">Solo administradores (id_rol = 1)</p>
                                <p class="text-xs text-red-600 mt-1">Protege rutas administrativas</p>
                            </div>
                            
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <h3 class="font-bold text-orange-900 mb-2">CheckUserEnabled</h3>
                                <p class="text-sm text-orange-700">Verifica usuario habilitado</p>
                                <p class="text-xs text-orange-600 mt-1">Bloquea usuarios deshabilitados</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h3 class="font-bold text-blue-900 mb-2">LoginAttemptThrottle</h3>
                                <p class="text-sm text-blue-700">Máximo 5 intentos por minuto</p>
                                <p class="text-xs text-blue-600 mt-1">Previene ataques de fuerza bruta</p>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h3 class="font-bold text-green-900 mb-2">HandleCsrfErrors</h3>
                                <p class="text-sm text-green-700">Protección CSRF</p>
                                <p class="text-xs text-green-600 mt-1">Regenera tokens automáticamente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flujo de Autenticación -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-key mr-2 text-yellow-500"></i>Flujo de Autenticación
                    </h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <div class="flex flex-wrap justify-center items-center space-x-4 space-y-2">
                            <div class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">
                                1. Login
                            </div>
                            <i class="fas fa-arrow-right text-gray-400"></i>
                            <div class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm">
                                2. Throttle
                            </div>
                            <i class="fas fa-arrow-right text-gray-400"></i>
                            <div class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm">
                                3. Auth
                            </div>
                            <i class="fas fa-arrow-right text-gray-400"></i>
                            <div class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm">
                                4. UserEnabled
                            </div>
                            <i class="fas fa-arrow-right text-gray-400"></i>
                            <div class="bg-purple-500 text-white px-4 py-2 rounded-lg text-sm">
                                5. AdminRole
                            </div>
                            <i class="fas fa-arrow-right text-gray-400"></i>
                            <div class="bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm">
                                6. Controller
                            </div>
                        </div>
                        <p class="text-center text-gray-600 mt-4 text-sm">
                            Cada paso valida permisos antes de continuar al siguiente
                        </p>
                    </div>
                </div>
            </div>

            <!-- Matriz de Permisos -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-table mr-2 text-indigo-500"></i>Matriz de Permisos por Rol
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Función</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Admin (1)</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado (2)</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente (3)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Gestionar Usuarios</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-green-600 text-xl">✅</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-red-600 text-xl">❌</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-red-600 text-xl">❌</span></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Gestionar Roles</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-green-600 text-xl">✅</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-blue-600 text-xl">👁️</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-red-600 text-xl">❌</span></td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Gestionar Prendas</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-green-600 text-xl">✅</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-red-600 text-xl">❌</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-red-600 text-xl">❌</span></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Ver Clientes</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-green-600 text-xl">✅</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-green-600 text-xl">✅</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-red-600 text-xl">❌</span></td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Crear Pedidos</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-green-600 text-xl">✅</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-green-600 text-xl">✅</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-green-600 text-xl">✅</span></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Ver Panel Sistema</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-green-600 text-xl">✅</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-red-600 text-xl">❌</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><span class="text-red-600 text-xl">❌</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-sm text-gray-600">
                        <p><span class="text-green-600">✅</span> Acceso completo | <span class="text-blue-600">👁️</span> Solo lectura | <span class="text-red-600">❌</span> Sin acceso</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
