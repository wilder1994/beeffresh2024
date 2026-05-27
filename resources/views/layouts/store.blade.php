<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="bf-cart-add-url" content="{{ route('carrito.agregar') }}">
    @include('layouts.partials.realtime-meta')
    <title>@yield('titulo', 'BEEF FRESH · Tienda')</title>
    @include('layouts.partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bf-panel-bg text-[var(--bf-ink)] antialiased">
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

    @guest
        @php
            $openRegisterConfirm = request('registro') === 'confirm';
            $openRegisterModal = $errors->hasAny(\App\Http\Requests\Auth\RegisterCustomerRequest::FIELD_KEYS);
        @endphp
        <x-auth.register-modals :open-confirm="$openRegisterConfirm" :open-register="$openRegisterModal" />
    @endguest

    @auth
        <x-account.profile-dialog />
        <x-notifications.center-dialog />
    @endauth

    @stack('scripts')
    {{-- Si hubo modal/dialog de depuración (p. ej. Livewire) u overflow bloqueado, al entrar a la tienda se normaliza el documento --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('dialog[open]').forEach(function (d) {
                try { d.close(); } catch (e) {}
            });
            document.querySelectorAll('#livewire-error').forEach(function (el) {
                el.remove();
            });
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
        });
    </script>
</body>
</html>
