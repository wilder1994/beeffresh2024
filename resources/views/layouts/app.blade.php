<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('titulo', 'BEEF FRESH')</title>

    @include('layouts.partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bf-panel-bg min-h-screen antialiased text-[var(--bf-ink)]">

@if(auth()->check() && auth()->user()->isStaff())
    <div x-data="staffLayout()" class="min-h-screen" x-on:keydown.escape.window="closeMobileMenu()">
        {{-- x-if: el scrim no permanece en el DOM al cerrar (evita capa bloqueando clics si Alpine queda desincronizado) --}}
        <template x-if="mobileMenuOpen">
            <div
                x-transition.opacity
                x-on:click="closeMobileMenu()"
                class="fixed inset-0 bg-black/40 z-30 lg:hidden"
            ></div>
        </template>

        <button
            type="button"
            x-show="sidebarCollapsed"
            x-transition
            x-on:click="toggleDesktopSidebar()"
            class="fixed left-0 top-1/2 -translate-y-1/2 z-30 hidden lg:flex items-center justify-center w-7 sm:w-8 min-h-[4.5rem] rounded-r-lg bg-[var(--bf-rust-deep)] text-white shadow-lg border border-l-0 border-white/10 hover:brightness-110"
            title="Mostrar menú lateral"
            aria-label="Mostrar menú lateral"
            x-cloak
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex flex-col bg-[var(--bf-rust-deep)] text-white w-[min(17rem,calc(100vw-1rem))] sm:w-[17rem] border-r border-black/20 shadow-xl transition-transform duration-300 ease-out min-h-screen overflow-hidden"
            x-bind:class="[ mobileMenuOpen ? 'max-lg:translate-x-0' : 'max-lg:-translate-x-full', sidebarCollapsed ? 'lg:-translate-x-full' : 'lg:translate-x-0' ]"
            role="navigation"
            aria-label="Menú principal"
        >
            @include('layouts.partials.sidebar-staff')
        </aside>

        <div
            class="min-h-screen flex flex-col transition-[padding] duration-300 ease-out staff-content"
            x-bind:class="sidebarCollapsed ? 'lg:pl-0' : 'lg:pl-[17rem]'"
        >
            <header class="sticky top-0 z-20 flex items-center gap-2 px-3 py-2 sm:py-2.5 bg-[var(--bf-rust-deep)] text-white shadow-md border-b border-black/15 lg:hidden">
                <button type="button" x-on:click="openMobileMenu()" class="btn btn-square btn-sm bg-white/10 border-white/30 text-white shrink-0" aria-label="Abrir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <span class="font-semibold text-xs sm:text-sm truncate font-brand tracking-tight">BEEF FRESH · Panel</span>
            </header>

            <main class="flex-grow flex flex-col min-w-0">
                @include('layouts.partials.flash-alerts')
                @hasSection('cabecera')
                    <div class="bg-[var(--bf-red)] my-2 sm:my-3 md:my-4 text-center mx-2 sm:mx-3 md:mx-4 rounded-lg md:rounded-xl overflow-hidden shadow border border-black/10">
                        <h1 class="staff-page-banner font-brand leading-tight">@yield('cabecera')</h1>
                    </div>
                @endif

                <div class="staff-main-inner flex-1 pb-6">
                    @yield('contenido')
                </div>
            </main>

            <footer class="shrink-0 py-2 sm:py-2.5 px-2 text-center border-0 bg-transparent" role="contentinfo" aria-label="Aviso legal">
                @include('layouts.footer')
            </footer>
        </div>
    </div>
@else
    <div class="min-h-screen flex flex-col">
        <header>
            @include('layouts.navbar')
        </header>

        <main class="flex-grow">
            @include('layouts.partials.flash-alerts')
            @hasSection('cabecera')
                <div class="bg-[var(--bf-red)] my-2 sm:my-4 text-center rounded-xl mx-2 md:mx-4 shadow border border-black/10">
                    <h1 class="staff-page-banner font-brand m-3 md:m-4">@yield('cabecera')</h1>
                </div>
            @endif

            <div class="container mx-auto px-3 sm:px-4">
                @yield('contenido')
            </div>
        </main>

        <footer class="shrink-0 py-2 sm:py-3 px-2 text-center border-0 bg-transparent" role="contentinfo" aria-label="Aviso legal">
            @include('layouts.footer')
        </footer>
    </div>
@endif

@auth
    <x-account.profile-dialog />
@endauth

<x-bf.confirm-dialog />

@livewireScriptConfig
</body>
</html>
