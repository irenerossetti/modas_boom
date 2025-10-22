<nav class="bg-boom-cream-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-end h-16 items-center space-x-4">
            <!-- Botón Hacer Pedido - Solo para clientes y empleados -->
            @if(Auth::check() && in_array(Auth::user()->id_rol, [2, 3]))
                <a href="{{ route('pedidos.cliente-crear') }}" 
                   class="inline-flex items-center px-4 py-2 bg-boom-rose-dark hover:bg-boom-rose-light text-white font-bold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    @if(Auth::user()->id_rol == 2)
                        <span class="hidden lg:inline">Hacer Pedido Personal</span>
                        <span class="lg:hidden">Mi Pedido</span>
                    @else
                        <span class="hidden lg:inline">Hacer Pedido</span>
                        <span class="lg:hidden">Pedido</span>
                    @endif
                </a>
            @endif

            <!-- Botón adicional para ver catálogo - Solo para clientes -->
            @if(Auth::check() && Auth::user()->id_rol == 3)
                <a href="{{ route('catalogo.index') }}" 
                   class="inline-flex items-center px-3 py-2 bg-boom-cream-300 hover:bg-boom-cream-400 text-boom-text-dark font-semibold rounded-lg transition-colors duration-300 shadow-sm hover:shadow-md border border-boom-cream-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span class="hidden lg:inline">Ver Catálogo</span>
                    <span class="lg:hidden">Catálogo</span>
                </a>
            @endif

            <!-- Dropdown del usuario -->
            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-boom-text-medium bg-transparent hover:text-boom-text-dark focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->nombre }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Opciones específicas por rol -->
                        @if(Auth::user()->id_rol == 1) <!-- Administrador -->
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('pedidos.index') }}">
                                <i class="fas fa-shopping-bag mr-2"></i>Gestionar Pedidos
                            </a>
                        @elseif(Auth::user()->id_rol == 2) <!-- Empleado -->
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('empleado.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('pedidos.mis-pedidos') }}">
                                <i class="fas fa-list mr-2"></i>Mis Pedidos
                            </a>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('pedidos.empleado-crear') }}">
                                <i class="fas fa-plus mr-2"></i>Crear Pedido Cliente
                            </a>
                        @elseif(Auth::user()->id_rol == 3) <!-- Cliente -->
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('pedidos.mis-pedidos') }}">
                                <i class="fas fa-list mr-2"></i>Mis Pedidos
                            </a>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('catalogo.index') }}">
                                <i class="fas fa-images mr-2"></i>Catálogo
                            </a>
                        @endif

                        <!-- Separador -->
                        <div class="border-t border-boom-cream-300 my-1"></div>

                        <!-- Opciones comunes -->
                        <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user mr-2"></i>{{ __('Perfil') }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-red-title hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Cerrar sesión') }}
                            </a>
                        </form>

                        <!-- Botón Hacer Pedido para clientes - después de cerrar sesión -->
                        @if(Auth::check() && Auth::user()->id_rol == 3)
                            <div class="border-t border-boom-cream-300 my-1"></div>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-white bg-boom-rose-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out font-bold" href="{{ route('pedidos.cliente-crear') }}">
                                <i class="fas fa-plus mr-2"></i>🛍️ Hacer Pedido
                            </a>
                        @endif
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Versión móvil -->
            <div class="flex items-center sm:hidden space-x-1">
                <!-- Botón Hacer Pedido móvil -->
                @if(Auth::check() && in_array(Auth::user()->id_rol, [2, 3]))
                    <a href="{{ route('pedidos.cliente-crear') }}" 
                       class="inline-flex items-center px-3 py-2 bg-boom-rose-dark hover:bg-boom-rose-light text-white font-bold rounded-lg transition-colors duration-300 text-sm shadow-md">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        @if(Auth::user()->id_rol == 2)
                            Mi Pedido
                        @else
                            Pedido
                        @endif
                    </a>
                @endif

                <!-- Botón Catálogo móvil - Solo para clientes -->
                @if(Auth::check() && Auth::user()->id_rol == 3)
                    <a href="{{ route('catalogo.index') }}" 
                       class="inline-flex items-center px-2 py-2 bg-boom-cream-300 hover:bg-boom-cream-400 text-boom-text-dark font-medium rounded-lg transition-colors duration-300 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </a>
                @endif

                <!-- Dropdown móvil -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-2 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-boom-text-medium bg-transparent hover:text-boom-text-dark focus:outline-none transition ease-in-out duration-150">
                            <div class="truncate max-w-20">{{ Auth::user()->nombre }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Mismo contenido que la versión desktop -->
                        @if(Auth::user()->id_rol == 1) <!-- Administrador -->
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('pedidos.index') }}">
                                <i class="fas fa-shopping-bag mr-2"></i>Gestionar Pedidos
                            </a>
                        @elseif(Auth::user()->id_rol == 2) <!-- Empleado -->
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('empleado.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('pedidos.mis-pedidos') }}">
                                <i class="fas fa-list mr-2"></i>Mis Pedidos
                            </a>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('pedidos.empleado-crear') }}">
                                <i class="fas fa-plus mr-2"></i>Crear Pedido Cliente
                            </a>
                        @elseif(Auth::user()->id_rol == 3) <!-- Cliente -->
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('pedidos.mis-pedidos') }}">
                                <i class="fas fa-list mr-2"></i>Mis Pedidos
                            </a>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('catalogo.index') }}">
                                <i class="fas fa-images mr-2"></i>Catálogo
                            </a>
                        @endif

                        <div class="border-t border-boom-cream-300 my-1"></div>

                        <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-text-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user mr-2"></i>{{ __('Perfil') }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-boom-red-title hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Cerrar sesión') }}
                            </a>
                        </form>

                        <!-- Botón Hacer Pedido para clientes - versión móvil -->
                        @if(Auth::check() && Auth::user()->id_rol == 3)
                            <div class="border-t border-boom-cream-300 my-1"></div>
                            <a class="block w-full px-4 py-2 text-start text-sm leading-5 text-white bg-boom-rose-dark hover:bg-boom-rose-light focus:outline-none focus:bg-boom-rose-light transition duration-150 ease-in-out font-bold" href="{{ route('pedidos.cliente-crear') }}">
                                <i class="fas fa-plus mr-2"></i>🛍️ Hacer Pedido
                            </a>
                        @endif
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
</div>