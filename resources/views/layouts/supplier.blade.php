<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Portal proveedores · BEEF FRESH')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500">Proveedores</p>
                <h1 class="text-lg font-semibold text-[var(--bf-red)]">BEEF FRESH</h1>
            </div>
            <nav class="flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ route('supplier.home') }}" class="px-3 py-2 rounded-lg hover:bg-slate-100 {{ request()->routeIs('supplier.home') ? 'bg-slate-100 font-medium' : '' }}">Resumen</a>
                <a href="{{ route('supplier.contact') }}" class="px-3 py-2 rounded-lg hover:bg-slate-100 {{ request()->routeIs('supplier.contact') ? 'bg-slate-100 font-medium' : '' }}">Contacto</a>
                <a href="{{ route('home') }}" class="text-slate-600 hover:text-[var(--bf-red)]">Ver tienda pública</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-red-700 hover:underline">Salir</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto w-full px-4 py-8">
        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-900 text-sm">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="py-6 text-center text-xs text-slate-500 border-t border-slate-200 bg-white">
        Portal de proveedores · BEEF FRESH
    </footer>
</body>
</html>
