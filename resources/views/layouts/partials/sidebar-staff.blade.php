@php
    use App\Models\Logo;
    $logoPrincipal = Logo::where('tipo', 'principal')->first();
    $logoPrincipalSrc = ($logoPrincipal && $logoPrincipal->imagen)
        ? asset('storage/logos/'.$logoPrincipal->imagen)
        : asset('logos/logo.jpeg');
    $isAdmin = auth()->user()->isAdmin();

    $navActive = 'flex items-center gap-2 sm:gap-3 px-2 sm:px-3 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition border-l-4';
    $navIdle = 'border-transparent text-white/90 hover:bg-white/10';
    $navOn = 'border-[var(--bf-gold)] bg-white/15 text-white';
@endphp

<div class="flex flex-col h-full min-h-0 flex-1 w-full min-w-0">
    <div class="p-3 sm:p-4 border-b border-white/10 shrink-0">
        <div class="flex items-start gap-2 sm:gap-3">
            <div class="relative shrink-0">
                <img src="{{ $logoPrincipalSrc }}" alt="BEEF FRESH" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover ring-2 ring-white/30">
                @if($isAdmin)
                    <form action="{{ route('admin.logo.update') }}" method="post" enctype="multipart/form-data" class="absolute -bottom-1 -right-1">
                        @csrf
                        <input type="file" name="imagen" id="sidebar-company-logo-input" class="hidden" accept="image/*" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
                        <label for="sidebar-company-logo-input" class="btn btn-circle btn-xs bg-white text-[var(--bf-rust-deep)] border-0 shadow-md hover:bg-[var(--bf-cream)] cursor-pointer" title="Cambiar logo de la empresa">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </label>
                    </form>
                @endif
            </div>
            <a href="{{ route('dashboard') }}" class="leading-tight flex-1 min-w-0 group pt-0.5">
                <span class="block font-brand tracking-tight text-sm sm:text-base text-white group-hover:text-[var(--bf-sun)]/95 transition">BEEF FRESH</span>
                <span class="text-[10px] sm:text-[11px] uppercase tracking-widest text-white/60">Panel interno</span>
            </a>
            <button type="button" x-on:click="toggleDesktopSidebar()" class="hidden lg:flex btn btn-ghost btn-xs btn-square shrink-0 text-white/90 hover:bg-white/10 border border-white/20" title="Ocultar menú lateral" aria-label="Ocultar menú lateral">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
            </button>
            <button type="button" x-on:click="closeMobileMenu()" class="lg:hidden btn btn-ghost btn-xs btn-square shrink-0 text-white/90 hover:bg-white/10" title="Cerrar menú" aria-label="Cerrar menú">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto min-h-0 py-3 sm:py-4 px-1.5 sm:px-2 space-y-0.5" x-on:click.capture="if ($event.target.closest('a')) closeMobileMenu()">
        <a href="{{ route('dashboard') }}" @class([$navActive, request()->routeIs('dashboard') ? $navOn : $navIdle])>
            <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            Inicio
        </a>

        @if($isAdmin)
            <p class="px-2 sm:px-3 pt-3 sm:pt-4 pb-1 text-[9px] sm:text-[10px] uppercase tracking-wider text-white/45 font-semibold">Operaciones</p>
            <a href="{{ route('admin.pedidos.index') }}" @class([$navActive, request()->routeIs('admin.pedidos.*') ? $navOn : $navIdle])>
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                Pedidos
            </a>
            <a href="{{ route('admin.users.index') }}" @class([$navActive, request()->routeIs('admin.users.*') ? $navOn : $navIdle])>
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                Usuarios
            </a>

            <p class="px-2 sm:px-3 pt-3 sm:pt-4 pb-1 text-[9px] sm:text-[10px] uppercase tracking-wider text-white/45 font-semibold">Catálogo</p>
            <a href="{{ route('productos.index') }}" @class([$navActive, request()->routeIs('productos.*') ? $navOn : $navIdle])>Productos</a>
            <a href="{{ route('videos.index') }}" @class([$navActive, request()->routeIs('videos.*') ? $navOn : $navIdle])>Videos</a>
            <a href="{{ route('recetas.index') }}" @class([$navActive, request()->routeIs('recetas.*') ? $navOn : $navIdle])>Recetas</a>
            <a href="{{ route('promociones.index') }}" @class([$navActive, request()->routeIs('promociones.*') ? $navOn : $navIdle])>Promociones</a>
            <a href="{{ route('cortes.index') }}" @class([$navActive, request()->routeIs('cortes.*') ? $navOn : $navIdle])>Cortes</a>
        @endif

        <p class="px-2 sm:px-3 pt-3 sm:pt-4 pb-1 text-[9px] sm:text-[10px] uppercase tracking-wider text-white/45 font-semibold">Nosotros</p>
        <a href="{{ route('nosotros') }}" target="_blank" rel="noopener" class="flex items-center gap-2 sm:gap-3 px-2 sm:px-3 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium border-l-4 border-transparent text-white/90 hover:bg-white/10">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
            Ver en tienda
        </a>
        @if($isAdmin)
            <a href="{{ route('admin.empresa.edit') }}" @class([$navActive, request()->routeIs('admin.empresa.*') ? $navOn : $navIdle])>
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Editar contenido
            </a>
        @endif
    </nav>

    <div class="p-2 sm:p-3 border-t border-white/10 bg-black/20 shrink-0">
        <div class="dropdown dropdown-top dropdown-end w-full">
            <label tabindex="0" class="flex items-center gap-2 sm:gap-3 px-2 py-2 rounded-lg cursor-pointer hover:bg-white/10 w-full min-w-0">
                <div class="avatar shrink-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full ring ring-white/30 overflow-hidden">
                        <x-user-avatar :user="auth()->user()" size="h-9 w-9" />
                    </div>
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <span class="block text-xs sm:text-sm font-semibold truncate">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] sm:text-[11px] text-white/60">Cuenta</span>
                </div>
                <svg class="w-4 h-4 shrink-0 opacity-70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
            </label>
            <ul tabindex="0" class="dropdown-content menu bg-[#fffaf5] text-gray-900 rounded-box shadow-lg w-52 z-[100] border border-amber-100 mb-2 text-sm">
                <li><a href="{{ route('profile.edit') }}">Mi perfil</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">Cerrar sesión</a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
