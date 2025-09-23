<x-app-layout>
    <div class="bg-boom-cream-200 min-h-screen">
        
        <main class="p-4 sm:p-6 lg:p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-boom-text-dark">Dashboard Administrativo</h1>
                <button class="bg-boom-red-report hover:opacity-90 text-white font-bold py-2 px-4 rounded-lg shadow">
                    Generar Reporte
                </button>
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
                    <div class="bg-boom-cream-300 hover:bg-boom-rose-light cursor-pointer p-6 rounded-xl shadow text-center">
                        <p class="font-bold text-boom-text-dark">Gestionar Usuarios</p>
                    </div>
                    <div class="bg-boom-cream-300 hover:bg-boom-rose-light cursor-pointer p-6 rounded-xl shadow text-center">
                        <p class="font-bold text-boom-text-dark">Gestionar Inventario</p>
                    </div>
                    <div class="bg-boom-cream-300 hover:bg-boom-rose-light cursor-pointer p-6 rounded-xl shadow text-center">
                        <p class="font-bold text-boom-text-dark">Programar Cita</p>
                    </div>
                    <div class="bg-boom-cream-300 hover:bg-boom-rose-light cursor-pointer p-6 rounded-xl shadow text-center">
                        <p class="font-bold text-boom-text-dark">Ver Reportes</p>
                    </div>
                </div>
            </div>

        </main>
    </div>
</x-app-layout>