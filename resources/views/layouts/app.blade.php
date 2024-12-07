<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('titulo','Minimercado2024')</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f5b327]">
        <header>
            {{-- Navbar --}}
            @include('layouts.navbar')
        </header>
        <main>
            <div class="bg-[#7c2d12] my-4 text-center">
                <h1 class="text-lg font-semibold m-4 uppercase">@yield('cabecera')</h1>
            </div>
            @yield('contenido')
        </main>
        <footer class="footer items-center p-4 bg-[#7c2d12] text-neutral-content">
            @include('layouts.footer')
        </footer>
    </body>
</html>