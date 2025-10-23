<section>
    <header>
        <h2 class="text-lg font-medium text-boom-text-dark">
            Información del Perfil
        </h2>

        <p class="mt-1 text-sm text-boom-text-medium">
            Actualiza la información de tu perfil y dirección de email.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="nombre" class="block text-sm font-medium text-boom-text-dark">Nombre</label>
            <input id="nombre" name="nombre" type="text" class="mt-1 block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" :value="old('nombre', $user->nombre)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2 text-boom-red-title text-sm" :messages="$errors->get('nombre')" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-boom-text-dark">Email</label>
            <input id="email" name="email" type="email" class="mt-1 block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2 text-boom-red-title text-sm" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-boom-text-dark">
                        Tu dirección de email no está verificada.

                        <button form="send-verification" class="underline text-sm text-boom-text-medium hover:text-boom-red-title rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-boom-red-title">
                            Haz clic aquí para reenviar el email de verificación.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Un nuevo enlace de verificación ha sido enviado a tu dirección de email.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bg-boom-red-report hover:bg-boom-red-title text-white font-bold py-2 px-4 rounded-lg transition-colors">
                Guardar
            </button>

            @if (session('status') === 'profile-updated')
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
