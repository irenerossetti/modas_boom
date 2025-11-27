<style>
    .contenedor{
        transition: transform 0.3s ease;
        animation: fadeIn 0.1s infinite alternate-reverse;
    }
    .contenedor:hover{
        transform: scale(1.05);
        animation: none;
    }
    #botoncito{
        transition: transform 0.5s ease;
    }
    #botoncito:hover{
        animation: fadeIn 0.5s infinite ;
    }

    @keyframes fadeIn{
        0% {opacity: 0.3;}
        100% {opacity: 1;}
    }

</style>
@extends('layouts.app')

@section('content')
<div class="bg-boom-cream-200 min-h-screen">
    
    <main class="p-4 sm:p-6 lg:p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-boom-text-dark">Dashboard Administrativo</h1>
                <div>
                    <!-- Button that opens the modal -->
                    <button id="openReportModal" type="button" class="bg-boom-red-report text-white font-bold py-2 px-4 rounded-lg shadow">Generar Reporte</button>
                </div>
                
                <!-- Modal para generar reportes -->
                <div id="reportModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
                    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl">
                        <h2 class="text-xl font-semibold mb-4">Generar Reporte</h2>
                        <form method="POST" action="{{ route('reportes.generate') }}">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="format" class="block text-sm font-medium text-boom-text-dark mb-1">Formato</label>
                                    <select name="format" id="format" class="w-full rounded-md border-boom-cream-300 shadow-sm" required>
                                        <option value="json">JSON</option>
                                        <option value="csv">CSV</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="desde" class="block text-sm font-medium text-boom-text-dark mb-1">Desde</label>
                                    <input type="date" name="desde" id="desde" class="w-full rounded-md border-boom-cream-300 shadow-sm" required>
                                </div>
                                <div>
                                    <label for="hasta" class="block text-sm font-medium text-boom-text-dark mb-1">Hasta</label>
                                    <input type="date" name="hasta" id="hasta" class="w-full rounded-md border-boom-cream-300 shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-boom-text-dark mb-1">Secciones</label>
                                    <div class="space-y-1">
                                        <label class="inline-flex items-center"><input type="checkbox" name="sections[]" value="productos" checked> Productos</label>
                                        <label class="inline-flex items-center"><input type="checkbox" name="sections[]" value="telas" checked> Telas</label>
                                        <label class="inline-flex items-center"><input type="checkbox" name="sections[]" value="ventas" checked> Ventas</label>
                                        <label class="inline-flex items-center"><input type="checkbox" name="sections[]" value="compras" checked> Compras</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end space-x-2">
                                <button type="button" id="closeReportModal" class="px-4 py-2 rounded-md bg-gray-200">Cancelar</button>
                                <button type="submit" class="px-4 py-2 rounded-md bg-boom-red-report text-white">Generar</button>
                            </div>
                        </form>
                    </div>
                </div>
                
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-boom-text-medium">Total Usuarios</h3>
                    <p class="text-4xl font-bold text-boom-text-dark mt-2">{{ $totalUsuarios ?? '0' }}</p>
                    <p class="text-sm text-green-500 mt-1">+12% desde el mes pasado</p>
                </div>
                <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-boom-text-medium">Pedidos Activos</h3>
                    <p class="text-4xl font-bold text-boom-text-dark mt-2">{{ $pedidosActivos ?? '0' }}</p>
                    <p class="text-sm text-boom-text-light mt-1">+5 nuevos hoy</p>
                </div>
                <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-boom-text-medium">Inventario</h3>
                    <p class="text-4xl font-bold text-boom-text-dark mt-2">456</p>
                    <p class="text-sm text-boom-text-light mt-1">Artículos en stock</p>
                </div>
                <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-boom-text-medium">Ingresos</h3>
                    <p class="text-4xl font-bold text-boom-text-dark mt-2">$45,231</p>
                    <p class="text-sm text-green-500 mt-1">+15% este mes</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="lg:col-span-2 bg-boom-cream-100 p-6 rounded-xl shadow">
                    <h3 class="font-bold text-xl text-boom-text-dark mb-4">Pedidos Recientes</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center bg-boom-cream-300 p-4 rounded-lg">
                            <div>
                                <p class="font-bold text-boom-text-dark">Maria Garcia</p>
                                <p class="text-sm text-boom-text-medium">Pedido #001</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-lg text-boom-text-dark">$150</p>
                                <span class="text-xs font-semibold text-white bg-boom-red-processing px-2 py-1 rounded-full">En proceso</span>
                            </div>
                        </div>
                         <div class="flex justify-between items-center bg-boom-cream-300 p-4 rounded-lg">
                            <div>
                                <p class="font-bold text-boom-text-dark">Juan Pérez</p>
                                <p class="text-sm text-boom-text-medium">Pedido #002</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-lg text-boom-text-dark">$89</p>
                                <span class="text-xs font-semibold text-white bg-boom-red-completed px-2 py-1 rounded-full">Completado</span>
                            </div>
                        </div>
                        </div>
                </div>

                <div class="bg-boom-cream-100 p-6 rounded-xl shadow">
                     <h3 class="font-bold text-xl text-boom-text-dark mb-4">Tareas y Alertas</h3>
                     <div class="space-y-4">
                        <div class="bg-boom-rose-light/50 border-l-4 border-boom-red-title p-4 rounded-r-lg">
                            <p class="font-bold text-boom-red-title">Stock bajo</p>
                            <p class="text-sm text-boom-text-medium">Tela de algodón - Solo 5 metros restantes</p>
                        </div>
                        <div class="bg-boom-cream-300 p-4 rounded-lg">
                            <p class="font-bold text-boom-text-dark">Entrega programada</p>
                            <p class="text-sm text-boom-text-medium">3 pedidos para entregar mañana</p>
                        </div>
                     </div>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-xl text-boom-text-dark mb-4">Acciones Rápidas</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="{{ route('users.create') }}" class="bg-boom-cream-300 hover:bg-boom-rose-light cursor-pointer p-6 rounded-xl shadow text-center border-2 border-boom-cream-400 transition-all duration-200 hover:shadow-lg hover:border-boom-red-title">
                        <svg class="w-8 h-8 mx-auto mb-2 text-boom-red-title" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        <p class="font-bold text-boom-text-dark">Crear Usuario</p>
                    </a>
                    <a href="{{ route('clientes.create') }}" class="bg-boom-cream-300 hover:bg-boom-rose-light cursor-pointer p-6 rounded-xl shadow text-center border-2 border-boom-cream-400 transition-all duration-200 hover:shadow-lg hover:border-boom-red-title">
                        <svg class="w-8 h-8 mx-auto mb-2 text-boom-red-title" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <p class="font-bold text-boom-text-dark">Crear Cliente</p>
                    </a>
                    <a href="{{ route('roles.index') }}" class="bg-boom-cream-300 hover:bg-boom-rose-light cursor-pointer p-6 rounded-xl shadow text-center border-2 border-boom-cream-400 transition-all duration-200 hover:shadow-lg hover:border-boom-red-title">
                        <svg class="w-8 h-8 mx-auto mb-2 text-boom-red-title" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <p class="font-bold text-boom-text-dark">Gestionar Roles</p>
                    </a>
                    <a href="{{ route('clientes.index') }}" class="bg-boom-cream-300 hover:bg-boom-rose-light cursor-pointer p-6 rounded-xl shadow text-center border-2 border-boom-cream-400 transition-all duration-200 hover:shadow-lg hover:border-boom-red-title">
                        <svg class="w-8 h-8 mx-auto mb-2 text-boom-red-title" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="font-bold text-boom-text-dark">Ver Reportes</p>
                    </a>
                </div>
            </div>

    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const openBtn = document.getElementById('openReportModal');
    const closeBtn = document.getElementById('closeReportModal');
    const modal = document.getElementById('reportModal');
    if (openBtn && modal) {
        openBtn.addEventListener('click', function(){
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    }
    if (closeBtn && modal) {
        closeBtn.addEventListener('click', function(){
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    }
});
</script>
@endpush