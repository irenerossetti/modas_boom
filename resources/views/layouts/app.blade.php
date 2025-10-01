<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-boom-cream-300">
            <aside class="w-64 bg-boom-cream-100 p-6 shadow-lg">
                <div class="flex items-center mb-8">
                    <img src="{{ asset('images/logo_boom.jpg') }}" alt="Modas Boom Logo" class="h-12 w-auto mr-3">
                    <div>
                        <h2 class="font-bold text-lg text-boom-text-dark">Modas Boom</h2>
                        <p class="text-sm text-boom-text-medium">Taller de Ropa</p>
                    </div>
                </div>

                <nav class="space-y-2">
                    <a href="#" class="flex items-center p-2 text-boom-text-dark bg-boom-rose-light rounded-lg font-semibold">
                        <span class="ml-3">Dashboard</span>
                    </a>
                    <a href="{{ route('users.index') }}" class="flex items-center p-2 text-boom-text-medium hover:bg-boom-cream-200 rounded-lg">
                        <span class="ml-3">Usuarios</span>
                    
                    <a href="{{ route('clientes.index') }}" class="flex items-center p-2 text-boom-text-medium hover:bg-boom-cream-200 rounded-lg">
                        <span class="ml-3">Clientes</span>
                        
                    </a>
                    <a href="#" class="flex items-center p-2 text-boom-text-medium hover:bg-boom-cream-200 rounded-lg">
                        <span class="ml-3">Trabajadores</span>
                    </a>
                    <a href="#" class="flex items-center p-2 text-boom-text-medium hover:bg-boom-cream-200 rounded-lg">
                        <span class="ml-3">Inventario</span>
                    </a>
                     <a href="#" class="flex items-center p-2 text-boom-text-medium hover:bg-boom-cream-200 rounded-lg">
                        <span class="ml-3">Pedidos</span>
                    </a>
                    </nav>
            </aside>

            <div class="flex-1 flex flex-col">
                @include('layouts.navigation')

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>