<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-boom-text-dark">
            Eliminar Cuenta
        </h2>

        <p class="mt-1 text-sm text-boom-text-medium">
            Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar tu cuenta, descarga cualquier dato o información que desees conservar.
        </p>
    </header>

    <button
        type="button"
        class="bg-boom-red-report hover:bg-boom-red-title text-white font-bold py-2 px-4 rounded-lg transition-colors"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        Eliminar Cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-boom-text-dark">
                ¿Estás seguro de que quieres eliminar tu cuenta?
            </h2>

            <p class="mt-1 text-sm text-boom-text-medium">
                Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Por favor, ingresa tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta.
            </p>

            <div class="mt-6">
                <label for="password" class="block text-sm font-medium text-boom-text-dark">Contraseña</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark"
                    placeholder="Contraseña"
                />
                <x-input-error class="mt-2 text-boom-red-title text-sm" :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="bg-boom-cream-100 hover:bg-boom-cream-200 text-boom-text-dark font-bold py-2 px-4 rounded-lg transition-colors mr-3">
                    Cancelar
                </button>

                <button type="submit" class="bg-boom-red-report hover:bg-boom-red-title text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Eliminar Cuenta
                </button>
            </div>
        </form>
    </x-modal>
</section>
