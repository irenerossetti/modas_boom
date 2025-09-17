<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Modas Boom') }}</title>

  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen auth-bg flex items-center justify-center px-4 py-12">
  <main class="w-full max-w-xl card-glass rounded-xl2 p-8">
    <div class="flex flex-col items-center mb-6">
      <img src="{{ asset('images/logo_boom.jpg') }}" alt="Modas Boom" class="h-14 w-14 rounded-xl object-cover">
      <h1 class="mt-4 text-2xl font-bold text-[#B21724] tracking-tight">Modas Boom</h1>
      <p class="text-slate-600">Taller de Ropa</p>
    </div>
    {{ $slot }}
  </main>

  <p class="absolute bottom-4 left-1/2 -translate-x-1/2 text-xs text-slate-600">
    © {{ date('Y') }} Modas Boom
  </p>
</body>
</html>
