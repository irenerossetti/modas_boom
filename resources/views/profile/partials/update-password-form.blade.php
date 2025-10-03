<section>
    <header>
        <h2 class="text-lg font-medium text-boom-text-dark">
            Actualizar Contraseña
        </h2>

        <p class="mt-1 text-sm text-boom-text-medium">
            Asegúrate de que tu cuenta use una contraseña larga y aleatoria para mantenerla segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-boom-text-dark">Contraseña Actual</label>
            <input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" autocomplete="current-password" />
            <x-input-error class="mt-2 text-boom-red-title text-sm" :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-boom-text-dark">Nueva Contraseña</label>
            <input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" autocomplete="new-password" />
            <x-input-error class="mt-2 text-boom-red-title text-sm" :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-boom-text-dark">Confirmar Contraseña</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" autocomplete="new-password" />
            <x-input-error class="mt-2 text-boom-red-title text-sm" :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bg-boom-red-report hover:bg-boom-red-title text-white font-bold py-2 px-4 rounded-lg transition-colors">
                Guardar
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-boom-text-medium"
                >Guardado.</p>
            @endif
        </div>
    </form>
</section>
