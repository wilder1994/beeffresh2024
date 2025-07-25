<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BEEF FRESH') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        {{-- Barra superior --}}
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('storage/logos/logo.jpeg') }}" alt="BEEF FRESH" class="h-10 w-auto">
                <span class="text-xl font-bold text-red-600">BEEF FRESH</span>
            </a>

            {{-- Carrito --}}
            <a href="{{ route('carrito.ver') }}" class="relative text-gray-700 hover:text-red-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h14l1-5H6.4M7 13L5 6H3m4 7l-1 5h13m-8 0a2 2 0 11-4 0m8 0a2 2 0 104 0" />
                </svg>
                @php
                    $carrito = session('carrito', []);
                    $totalProductos = array_sum(array_column($carrito, 'cantidad'));
                @endphp
                @if ($totalProductos > 0)
                    <span id="contador-carrito" class="absolute ...">
                       {{ $totalProductos }}
                    </span>

                @endif
            </a>
        </header>

        {{-- Contenido dinámico --}}
        <main class="flex-1 w-full">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
