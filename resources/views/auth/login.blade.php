<x-guest-layout>
  {{-- Estado de sesión (recuperación de contraseña, etc.) --}}
  <x-auth-session-status class="mb-4" :status="session('status')" />

  <form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <div>
      <x-input-label for="email" value="Correo electrónico" />
      <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                    :value="old('email')" required autofocus autocomplete="username" />
      <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div x-data="{ show: false }" class="relative">
      <x-input-label for="password" value="Contraseña" />
      <input id="password" name="password" :type="show ? 'text' : 'password'"
             class="mt-1 block w-full rounded-md border-slate-300 focus:border-brand-salmon focus:ring-brand-salmon"
             required autocomplete="current-password" />
      <button type="button" @click="show=!show"
              class="absolute right-3 top-[38px] text-slate-500 hover:text-slate-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z" />
          <circle cx="12" cy="12" r="2.25" stroke-width="1.8"/>
        </svg>
      </button>
      <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div class="flex items-center justify-between">
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="remember"
               class="rounded border-slate-300 text-brand-rose focus:ring-brand-salmon">
        <span class="text-sm text-slate-600">Recordarme</span>
      </label>

      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}"
           class="text-sm font-medium text-brand-rose hover:text-brand-pink">¿Olvidaste tu contraseña?</a>
      @endif
    </div>

    <button class="btn-primary">Iniciar Sesión</button>

   {{--  <div class="rounded-lg bg-[#EFD2C9] text-slate-800/90 p-4">
      <p class="text-center font-medium mb-2">Credenciales de prueba:</p>
      <p><strong>Admin:</strong> admin@modasboom.com</p>
      <p><strong>Trabajador:</strong> trabajador@modasboom.com</p>
      <p><strong>Cliente:</strong> cualquier otro email</p>
    </div> --}}

    {{-- Registro --}}

    @if (Route::has('register'))
      <p class="text-center text-sm text-slate-600">
        ¿No tienes cuenta?
        <a href="{{ route('register') }}" class="font-medium text-brand-rose hover:text-brand-pink">Regístrate</a>
      </p>
    @endif
  </form>
</x-guest-layout>
