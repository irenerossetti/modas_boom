<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('scripts')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col lg:flex-row bg-boom-cream-300">
            <!-- Mobile menu button -->
            <div id="mobile-header" class="lg:hidden bg-boom-cream-100 p-4 flex justify-between items-center shadow-lg">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo_boom.jpg') }}" alt="Modas Boom Logo" class="h-8 w-auto mr-2">
                    <div>
                        <h2 class="font-bold text-sm text-boom-text-dark">Modas Boom</h2>
                        <p class="text-xs text-boom-text-medium">Taller de Ropa</p>
                    </div>
                </div>
                <button id="mobile-menu-toggle" class="text-boom-text-dark hover:text-boom-rose-dark">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Sidebar -->
            <aside id="sidebar" class="w-full lg:w-64 bg-boom-cream-100 shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out fixed lg:relative z-50 h-full lg:h-auto overflow-y-auto">
                <div class="p-4 lg:p-6">
                    <!-- Mobile header with close button -->
                    <div class="lg:hidden flex justify-between items-center mb-6 pb-4 border-b border-boom-cream-200">
                        <div class="flex items-center">
                            <img src="{{ asset('images/logo_boom.jpg') }}" alt="Modas Boom Logo" class="h-8 w-auto mr-2">
                            <div>
                                <h2 class="font-bold text-sm text-boom-text-dark">Modas Boom</h2>
                                <p class="text-xs text-boom-text-medium">Taller de Ropa</p>
                            </div>
                        </div>
                        <button id="mobile-menu-close" class="text-boom-text-dark hover:text-boom-rose-dark">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Desktop logo (hidden on mobile) -->
                    <div class="hidden lg:flex items-center mb-8">
                        <img src="{{ asset('images/logo_boom.jpg') }}" alt="Modas Boom Logo" class="h-12 w-auto mr-3">
                        <div>
                            <h2 class="font-bold text-lg text-boom-text-dark">Modas Boom</h2>
                            <p class="text-sm text-boom-text-medium">Taller de Ropa</p>
                        </div>
                    </div>

                    <nav class="space-y-1 lg:space-y-2">
                    @if(Auth::user()->id_rol == 1) <!-- Administrador -->
                        <a href="{{ route('dashboard') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('dashboard') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Dashboard</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('users.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Usuarios</span>
                        </a>
                        <a href="{{ route('roles.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('roles.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Roles</span>
                        </a>
                        <a href="{{ route('prendas.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('prendas.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Prendas</span>
                        </a>
                        <a href="{{ route('clientes.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('clientes.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Clientes</span>
                        </a>
                        <a href="{{ route('catalogo.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('catalogo.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Catálogo</span>
                        </a>
                        <a href="{{ route('pedidos.empleado-crear') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.empleado-crear') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Crear Pedido</span>
                        </a>
                        <a href="{{ route('pedidos.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.*') && !request()->routeIs('pedidos.empleado-crear') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Gestionar Pedidos</span>
                        </a>
                        <a href="{{ route('prendas.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('prendas.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Gestionar Prendas</span>
                        </a>
                        <a href="{{ route('bitacora.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('bitacora.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Bitácora</span>
                        </a>
                        <a href="{{ route('sistema.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('sistema.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Sistema</span>
                        </a>
                    @elseif(Auth::user()->id_rol == 2) <!-- Empleado -->
                        <a href="{{ route('empleado.dashboard') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('empleado.dashboard') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Dashboard Empleado</span>
                        </a>
                        <a href="{{ route('clientes.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('clientes.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Clientes</span>
                        </a>
                        <a href="{{ route('catalogo.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('catalogo.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Catálogo</span>
                        </a>
                        <!-- Opción para hacer pedido personal (empleado como cliente) -->
                        <a href="{{ route('pedidos.cliente-crear') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.cliente-crear') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Hacer Pedido</span>
                        </a>
                        <!-- Opción para crear pedidos para clientes (función empleado) -->
                        <a href="{{ route('pedidos.empleado-crear') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.empleado-crear') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Crear Pedido Cliente</span>
                        </a>
                        <!-- Ver mis pedidos personales -->
                        <a href="{{ route('pedidos.mis-pedidos') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.mis-pedidos') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Mis Pedidos</span>
                        </a>
                        <a href="{{ route('pedidos.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.*') && !request()->routeIs('pedidos.empleado-crear') && !request()->routeIs('pedidos.cliente-crear') && !request()->routeIs('pedidos.mis-pedidos') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Gestionar Pedidos</span>
                        </a>
                    @elseif(Auth::user()->id_rol == 3) <!-- Cliente -->
                        <a href="{{ route('cliente.dashboard') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('cliente.dashboard') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Mi Dashboard</span>
                        </a>
                        
                        <!-- Botón destacado para hacer pedido -->
                        <a href="{{ route('pedidos.cliente-crear') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-bold transition-all duration-300 {{ request()->routeIs('pedidos.cliente-crear') ? 'text-white bg-boom-rose-dark border border-boom-rose-dark shadow-lg' : 'text-white bg-boom-rose-dark hover:bg-boom-rose-light border border-boom-rose-dark shadow-md hover:shadow-lg' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="text-sm lg:text-base">🛍️ Hacer Pedido</span>
                        </a>
                        
                        <a href="{{ route('pedidos.mis-pedidos') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.mis-pedidos') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Mis Pedidos</span>
                        </a>
                        
                        <a href="{{ route('catalogo.index') }}" class="flex items-center p-2 lg:p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('catalogo.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="text-sm lg:text-base">Catálogo</span>
                        </a>
                    @endif
                    </nav>
                </div>
            </aside>

            <!-- Overlay for mobile menu -->
            <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

            <div class="flex-1 flex flex-col lg:ml-0">
                @include('layouts.navigation')

                <main class="flex-1 page-transition p-2 lg:p-0">
                    @yield('content')
                </main>

                <!-- Botón flotante para clientes -->
                @if(Auth::check() && Auth::user()->id_rol == 3)
                    <div class="fixed bottom-6 right-6 z-40">
                        <a href="{{ route('pedidos.cliente-crear') }}" 
                           class="inline-flex items-center justify-center w-14 h-14 bg-boom-rose-dark hover:bg-boom-rose-light text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110"
                           title="Hacer Pedido">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <script>
            // Mobile menu functionality
            document.addEventListener('DOMContentLoaded', function() {
                const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
                const mobileMenuClose = document.getElementById('mobile-menu-close');
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                const mobileHeader = document.getElementById('mobile-header');

                // Estado del menú
                let isMobileMenuOpen = false;

                function resetMobileState() {
                    // Resetear completamente el estado móvil
                    if (mobileHeader) {
                        mobileHeader.style.display = '';
                        mobileHeader.style.removeProperty('display');
                    }
                    if (sidebar) {
                        sidebar.classList.add('-translate-x-full');
                    }
                    if (overlay) {
                        overlay.classList.add('hidden');
                    }
                    document.body.classList.remove('overflow-hidden');
                    isMobileMenuOpen = false;
                }

                function openSidebar() {
                    if (window.innerWidth >= 1024) return; // No abrir en desktop
                    
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    if (mobileHeader) {
                        mobileHeader.style.display = 'none';
                    }
                    document.body.classList.add('overflow-hidden');
                    isMobileMenuOpen = true;
                }

                function closeSidebar() {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    if (mobileHeader) {
                        mobileHeader.style.display = 'flex';
                    }
                    document.body.classList.remove('overflow-hidden');
                    isMobileMenuOpen = false;
                }

                function handleResize() {
                    const isDesktop = window.innerWidth >= 1024;
                    
                    if (isDesktop) {
                        // En desktop: resetear todo el estado móvil
                        resetMobileState();
                    } else {
                        // En móvil: asegurar que el estado sea consistente
                        if (isMobileMenuOpen) {
                            // Si el menú debería estar abierto, asegurar que esté abierto
                            if (mobileHeader) {
                                mobileHeader.style.display = 'none';
                            }
                            sidebar.classList.remove('-translate-x-full');
                            overlay.classList.remove('hidden');
                        } else {
                            // Si el menú debería estar cerrado, asegurar que esté cerrado
                            if (mobileHeader) {
                                mobileHeader.style.display = 'flex';
                            }
                            sidebar.classList.add('-translate-x-full');
                            overlay.classList.add('hidden');
                        }
                    }
                }

                // Event listeners
                if (mobileMenuToggle) {
                    mobileMenuToggle.addEventListener('click', openSidebar);
                }

                if (mobileMenuClose) {
                    mobileMenuClose.addEventListener('click', closeSidebar);
                }

                if (overlay) {
                    overlay.addEventListener('click', closeSidebar);
                }

                // Close sidebar when clicking on a link (mobile)
                const sidebarLinks = sidebar.querySelectorAll('a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 1024) {
                            closeSidebar();
                        }
                    });
                });

                // Handle window resize con debounce para mejor rendimiento
                let resizeTimeout;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(handleResize, 100);
                });

                // Inicializar estado correcto al cargar
                handleResize();

                // Manejar cambios de orientación en móviles
                window.addEventListener('orientationchange', () => {
                    setTimeout(handleResize, 200);
                });
            });
        </script>
    </body>
</html>