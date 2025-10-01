<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8">
        <h1 class="text-3xl font-bold text-boom-text-dark mb-6">Gestión de Usuarios</h1>

        <div class="bg-boom-cream-100 p-5 rounded-xl shadow">
            <table class="w-full text-left">
                <thead class="text-boom-text-medium">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Rol</th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-boom-cream-200">
                    @foreach ($users as $user)
                    <tr class="text-boom-text-dark">
                        <td class="p-3">{{ $user->id_usuario }}</td>
                        <td class="p-3 font-bold">{{ $user->nombre }}</td>
                        <td class="p-3">{{ $user->email }}</td>
                        <td class="p-3">{{ $user->rol->nombre ?? 'Sin rol' }}</td>
                        <td class="p-3">
                            <a href="#" class="text-blue-500 hover:underline">Editar</a>
                            <a href="#" class="text-red-500 hover:underline ml-4">Eliminar</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>