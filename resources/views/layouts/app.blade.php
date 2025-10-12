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
        <div class="min-h-screen flex bg-boom-cream-300">
            <aside class="w-64 bg-boom-cream-100 p-6 shadow-lg">
                <div class="flex items-center mb-8">
                    <img src="{{ asset('images/logo_boom.jpg') }}" alt="Modas Boom Logo" class="h-12 w-auto mr-3">
                    <div>
                        <h2 class="font-bold text-lg text-boom-text-dark">Modas Boom</h2>
                        <p class="text-sm text-boom-text-medium">Taller de Ropa</p>
                    </div>
                </div>

                <nav class="space-y-2">
                    @if(Auth::user()->id_rol == 1) <!-- Administrador -->
                        <a href="{{ route('dashboard') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('dashboard') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('users.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <span>Usuarios</span>
                        </a>
                        <a href="{{ route('roles.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('roles.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span>Roles</span>
                        </a>
                        <a href="{{ route('clientes.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('clientes.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Clientes</span>
                        </a>
                        <a href="{{ route('catalogo.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('catalogo.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span>Catálogo</span>
                        </a>
                        <a href="{{ route('pedidos.empleado-crear') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.empleado-crear') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Crear Pedido</span>
                        </a>
                        <a href="{{ route('pedidos.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.*') && !request()->routeIs('pedidos.empleado-crear') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span>Gestionar Pedidos</span>
                        </a>
                        <a href="{{ route('prendas.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('prendas.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span>Gestionar Prendas</span>
                        </a>
                        <a href="{{ route('bitacora.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('bitacora.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Bitácora</span>
                        </a>
                    @elseif(Auth::user()->id_rol == 2) <!-- Empleado -->
                        <a href="{{ route('empleado.dashboard') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('empleado.dashboard') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                            </svg>
                            <span>Dashboard Empleado</span>
                        </a>
                        <a href="{{ route('clientes.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('clientes.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Clientes</span>
                        </a>
                        <a href="{{ route('catalogo.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('catalogo.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span>Catálogo</span>
                        </a>
                        <!-- Opción para hacer pedido personal (empleado como cliente) -->
                        <a href="{{ route('pedidos.cliente-crear') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.cliente-crear') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span>Hacer Pedido</span>
                        </a>
                        <!-- Opción para crear pedidos para clientes (función empleado) -->
                        <a href="{{ route('pedidos.empleado-crear') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.empleado-crear') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Crear Pedido Cliente</span>
                        </a>
                        <!-- Ver mis pedidos personales -->
                        <a href="{{ route('pedidos.mis-pedidos') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.mis-pedidos') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span>Mis Pedidos</span>
                        </a>
                        <a href="{{ route('pedidos.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.*') && !request()->routeIs('pedidos.empleado-crear') && !request()->routeIs('pedidos.cliente-crear') && !request()->routeIs('pedidos.mis-pedidos') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span>Gestionar Pedidos</span>
                        </a>
                    @elseif(Auth::user()->id_rol == 3) <!-- Cliente -->
                        <a href="{{ route('pedidos.cliente-crear') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.cliente-crear') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span>Hacer Pedido</span>
                        </a>
                        <a href="{{ route('pedidos.mis-pedidos') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('pedidos.mis-pedidos') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span>Mis Pedidos</span>
                        </a>
                        <a href="{{ route('catalogo.index') }}" class="flex items-center p-3 rounded-lg font-semibold transition-all duration-300 {{ request()->routeIs('catalogo.*') ? 'text-boom-text-dark bg-boom-rose-light border border-boom-rose-dark shadow-md' : 'text-boom-text-medium hover:bg-boom-cream-200 border border-boom-cream-300 shadow-sm hover:shadow-md' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span>Catálogo</span>
                        </a>
                    @endif
                </nav>
            </aside>

            <div class="flex-1 flex flex-col">
                @include('layouts.navigation')

                <main class="flex-1 page-transition">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>