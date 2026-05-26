@php
    use App\Models\Logo;
    $logoPrincipal = Logo::where('tipo', 'principal')->first();
    $logoPrincipalSrc = ($logoPrincipal && $logoPrincipal->imagen)
        ? asset('storage/logos/'.$logoPrincipal->imagen)
        : asset('logos/logo.jpeg');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.realtime-meta')
    <title>@yield('titulo', 'Portal proveedores · BEEF FRESH')</title>
    @include('layouts.partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bf-panel-bg text-[var(--bf-ink)] antialiased">
    <header class="sticky top-0 z-50 border-b border-amber-100/80 bg-[var(--bf-brand)] text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('supplier.home') }}" class="flex items-center gap-3 shrink-0 group" title="BEEF FRESH · Portal proveedor">
                <img src="{{ $logoPrincipalSrc }}" alt="BEEF FRESH" class="h-11 w-11 rounded-full object-cover ring-2 ring-white/30">
                <div class="leading-tight">
                    <span class="font-brand tracking-tight text-lg">BEEF FRESH</span>
                    <span class="block text-[11px] uppercase tracking-widest text-white/75">Portal proveedores</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('supplier.home') }}" class="px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('supplier.home') ? 'bg-white/15' : '' }}">Resumen</a>
                <a href="{{ route('supplier.contact') }}" class="px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('supplier.contact') ? 'bg-white/15' : '' }}">Contacto</a>
                <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg hover:bg-white/10">Tienda pública</a>
            </nav>

            <div class="flex items-center gap-2 shrink-0 ml-auto">
                @auth
                    <x-notifications.bell variant="dark" />
                    <x-nav-user-menu :user="auth()->user()" variant="dark" />
                @endauth
            </div>
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

    @auth
        <x-account.profile-dialog />
    @endauth

    @stack('scripts')
</body>
</html>
