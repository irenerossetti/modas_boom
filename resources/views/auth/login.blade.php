<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-boom-cream-200">
        <div>
            <a href="/">
                <img src="{{ asset('images/logo_boom.jpg') }}" alt="Modas Boom Logo" class="w-24 h-auto">
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-boom-cream-100 shadow-lg overflow-hidden sm:rounded-lg border-2 border-boom-rose-dark">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block font-medium text-sm text-boom-text-dark mb-2">Email</label>
                    <input id="email" class="block mt-1 w-full border-2 border-boom-rose-dark focus:border-boom-red-title focus:ring-boom-red-title rounded-lg shadow-md px-3 py-2" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label for="password" class="block font-medium text-sm text-boom-text-dark mb-2">Contraseña</label>
                    <input id="password" class="block mt-1 w-full border-2 border-boom-rose-dark focus:border-boom-red-title focus:ring-boom-red-title rounded-lg shadow-md px-3 py-2" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-2 border-boom-rose-dark text-boom-red-title shadow-sm focus:ring-boom-red-title" name="remember">
                        <span class="ms-2 text-sm text-boom-text-medium">Recuérdame</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-boom-text-medium hover:text-boom-text-dark rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif

                    <button type="submit" class="ms-3 inline-flex items-center px-4 py-2 bg-boom-red-title border-2 border-boom-red-report rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-boom-red-report focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-boom-red-title transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>ml>