<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrarse - Modas Boom</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-boom-cream-200 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-4">
        <!-- Logo y título -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo_boom.jpg') }}" alt="Modas Boom Logo" class="h-16 w-auto mx-auto mb-4">
            <h1 class="text-3xl font-bold text-boom-text-dark">Crear Cuenta</h1>
            <p class="text-boom-text-medium mt-2">Únete a Modas Boom y descubre nuestras colecciones</p>
        </div>

        <!-- Formulario -->
        <div class="bg-boom-cream-100 rounded-2xl p-8 shadow-lg border-0">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Nombre -->
                <div class="mb-4">
                    <label for="nombre" class="block text-sm font-medium text-boom-text-dark mb-1">Nombre</label>
                    <input id="nombre" class="block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" type="text" name="nombre" :value="old('nombre')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2 text-boom-red-title text-sm" />
                </div>

                <!-- Apellido -->
                <div class="mb-4">
                    <label for="apellido" class="block text-sm font-medium text-boom-text-dark mb-1">Apellido</label>
                    <input id="apellido" class="block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" type="text" name="apellido" :value="old('apellido')" required autocomplete="family-name" />
                    <x-input-error :messages="$errors->get('apellido')" class="mt-2 text-boom-red-title text-sm" />
                </div>

                <!-- CI/NIT -->
                <div class="mb-4">
                    <label for="ci_nit" class="block text-sm font-medium text-boom-text-dark mb-1">CI/NIT</label>
                    <input id="ci_nit" class="block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" type="text" name="ci_nit" :value="old('ci_nit')" required autocomplete="off" />
                    <x-input-error :messages="$errors->get('ci_nit')" class="mt-2 text-boom-red-title text-sm" />
                </div>

                <!-- Teléfono -->
                <div class="mb-4">
                    <label for="telefono" class="block text-sm font-medium text-boom-text-dark mb-1">Teléfono</label>
                    <input id="telefono" class="block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" type="text" name="telefono" :value="old('telefono')" autocomplete="tel" />
                    <x-input-error :messages="$errors->get('telefono')" class="mt-2 text-boom-red-title text-sm" />
                </div>

                <!-- Dirección -->
                <div class="mb-4">
                    <label for="direccion" class="block text-sm font-medium text-boom-text-dark mb-1">Dirección</label>
                    <input id="direccion" class="block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" type="text" name="direccion" :value="old('direccion')" autocomplete="address" />
                    <x-input-error :messages="$errors->get('direccion')" class="mt-2 text-boom-red-title text-sm" />
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-boom-text-dark mb-1">Email</label>
                    <input id="email" class="block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-boom-red-title text-sm" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-boom-text-dark mb-1">Contraseña</label>
                    <input id="password" class="block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-boom-red-title text-sm" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-boom-text-dark mb-1">Confirmar Contraseña</label>
                    <input id="password_confirmation" class="block w-full rounded-lg border-boom-cream-300 bg-white shadow-sm focus:border-boom-red-title focus:ring-boom-red-title text-boom-text-dark" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-boom-red-title text-sm" />
                </div>

                <!-- Botón -->
                <button type="submit" class="w-full bg-boom-red-report hover:bg-boom-red-title text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-md">
                    Crear Cuenta
                </button>

                <!-- Enlace a login -->
                <div class="text-center mt-6">
                    <a href="{{ route('login') }}" class="text-boom-text-medium hover:text-boom-red-title text-sm underline">
                        ¿Ya tienes cuenta? Inicia Sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
