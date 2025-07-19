<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('titulo', 'BEEF FRESH')</title>

    {{-- Estilos y scripts compilados con Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5b327] min-h-screen flex flex-col">
    
    {{-- Header --}}
    <header>
        @include('layouts.navbar')
    </header>

    {{-- Contenido principal --}}
    <main class="flex-grow">
        @hasSection('cabecera')
            <div class="bg-[#7c2d12] my-4 text-center">
                <h1 class="text-lg font-semibold m-4 uppercase text-white">@yield('cabecera')</h1>
            </div>
        @endif

        <div class="container mx-auto px-4">
            @yield('contenido')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="footer items-center p-4 bg-[#7c2d12] text-white text-center">
        @include('layouts.footer')
    </footer>

</body>
</html>
