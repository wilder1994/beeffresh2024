<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'BEEF FRESH · Tienda')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;family=Playfair+Display:600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-[var(--bf-cream)] text-gray-900 antialiased">
    @include('components.nav-store')

    <main class="flex-1 w-full">
        @if(session('success'))
            <div class="max-w-5xl mx-auto px-4 pt-4">
                <div class="bg-green-100 border border-green-400 text-green-900 px-4 py-3 rounded-lg shadow-sm">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-5xl mx-auto px-4 pt-4">
                <div class="bg-red-100 border border-red-400 text-red-900 px-4 py-3 rounded-lg shadow-sm">{{ session('error') }}</div>
            </div>
        @endif
        @yield('content')
    </main>

    @include('layouts.partials.footer-store')
    @stack('scripts')
</body>
</html>
