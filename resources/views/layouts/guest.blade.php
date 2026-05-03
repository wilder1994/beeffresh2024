<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('titulo', config('app.name', 'BEEF FRESH'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-b from-[var(--bf-cream)] to-amber-50/80 text-gray-900 min-h-screen">
    <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 px-4">

        {{-- Marca + retorno a tienda --}}
        <div class="text-center mb-2">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-1 group">
                <img src="{{ asset('logos/logo.jpeg') }}" alt="BEEF FRESH" class="h-16 w-16 rounded-full object-cover ring-2 ring-[var(--bf-red)]/35 shadow group-hover:ring-[var(--bf-gold)] transition">
                <span class="text-lg font-semibold text-[var(--bf-red)]" style="font-family: Georgia, serif;">BEEF FRESH</span>
                <span class="text-xs text-gray-500 group-hover:text-[var(--bf-red)]">← Ir a la tienda</span>
            </a>
        </div>

        {{-- Contenedor de contenido dinámico --}}
        <div class="w-full sm:max-w-md mt-4 px-6 py-8 bg-white shadow-lg rounded-2xl border border-amber-100/80">
            @isset($slot)
                {{ $slot }}
            @else
                @yield('contenido')
            @endisset
        </div>
    </div>
</body>
</html>