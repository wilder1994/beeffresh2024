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
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0">

        {{-- Logo centrado --}}
        <div>
            <a href="/">
                <x-application-logo class="w-24 h-24 fill-current text-[#7c2d12]" />
            </a>
        </div>

        {{-- Contenedor de contenido dinámico --}}
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>
</html>