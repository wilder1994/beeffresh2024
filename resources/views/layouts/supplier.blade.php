<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Portal proveedores · BEEF FRESH')</title>
    @include('layouts.partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bf-panel-bg text-[var(--bf-ink)]">
    <header class="border-b border-amber-100/80 bg-white/95 backdrop-blur shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-[var(--bf-muted)]">Proveedores</p>
                <h1 class="text-lg font-brand text-[var(--bf-brand)]">BEEF FRESH</h1>
            </div>
            <nav class="flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ route('supplier.home') }}" class="px-3 py-2 rounded-xl hover:bg-[var(--bf-cream-muted)] {{ request()->routeIs('supplier.home') ? 'bg-[var(--bf-cream-muted)] font-medium text-[var(--bf-rust)]' : 'text-[var(--bf-ink)]' }}">Resumen</a>
                <a href="{{ route('supplier.contact') }}" class="px-3 py-2 rounded-xl hover:bg-[var(--bf-cream-muted)] {{ request()->routeIs('supplier.contact') ? 'bg-[var(--bf-cream-muted)] font-medium text-[var(--bf-rust)]' : 'text-[var(--bf-ink)]' }}">Contacto</a>
                <a href="{{ route('home') }}" class="text-[var(--bf-muted)] hover:text-[var(--bf-red)]">Ver tienda pública</a>
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

    <footer class="shrink-0 py-2 px-3 text-center border-0 bg-transparent mt-auto" role="contentinfo">
        @include('layouts.footer')
    </footer>
</body>
</html>
