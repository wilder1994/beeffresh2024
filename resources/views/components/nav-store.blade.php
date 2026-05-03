{{-- Navegación tienda: orientada al cliente; enlaces al panel interno o portal proveedor solo si aplica --}}
<header class="sticky top-0 z-50 shadow-md border-b border-[var(--bf-red)]/20 bg-[var(--bf-red)] text-white">
    <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
            <img src="{{ asset('logos/logo.jpeg') }}" alt="BEEF FRESH" class="h-11 w-11 rounded-full object-cover ring-2 ring-white/30">
            <div class="leading-tight">
                <span class="font-semibold tracking-tight text-lg" style="font-family: Georgia, 'Times New Roman', serif;">BEEF FRESH</span>
                <span class="block text-[11px] uppercase tracking-widest text-white/75">Carnes frescas · Tienda en línea</span>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
            <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('home') ? 'bg-white/15' : '' }}">Inicio</a>
            <a href="{{ route('productos.publico.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('productos.publico.*') ? 'bg-white/15' : '' }}">Catálogo</a>
            <a href="{{ route('carrito.ver') }}" class="px-3 py-2 rounded-lg hover:bg-white/10">Carrito</a>
        </nav>

        <div class="flex items-center gap-2">
            @auth
                @if(auth()->user()->isStaff())
                    <a href="{{ route('dashboard') }}" class="text-xs px-3 py-2 rounded-lg bg-[var(--bf-gold)] text-[var(--bf-red)] font-semibold hover:brightness-110">
                        Gestión interna
                    </a>
                @elseif(auth()->user()->isSupplier())
                    <a href="{{ route('supplier.home') }}" class="text-xs px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25">
                        Portal proveedor
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="text-xs px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25">
                        Mi cuenta
                    </a>
                @endif

                <div class="dropdown dropdown-end">
                    <button type="button" tabindex="0" class="btn btn-ghost btn-sm text-white border border-white/30">{{ auth()->user()->name }}</button>
                    <ul tabindex="0" class="dropdown-content menu bg-white text-gray-900 rounded-box z-[100] w-52 p-2 shadow-lg mt-2">
                        <li><a href="{{ route('profile.edit') }}">Mi perfil</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left">Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm bg-white/10 hover:bg-white/20 border border-white/40 text-white">Ingresar</a>
                <a href="{{ route('register') }}" class="btn btn-sm bg-[var(--bf-gold)] text-[var(--bf-red)] border-0 hover:brightness-105">Registrarse</a>
            @endauth

            <details class="dropdown dropdown-end md:hidden">
                <summary class="btn btn-ghost btn-circle text-white border border-white/30">☰</summary>
                <ul class="dropdown-content menu bg-white text-gray-900 rounded-box z-[100] w-52 p-2 shadow mt-2">
                    <li><a href="{{ route('home') }}">Inicio</a></li>
                    <li><a href="{{ route('productos.publico.index') }}">Catálogo</a></li>
                    <li><a href="{{ route('carrito.ver') }}">Carrito</a></li>
                </ul>
            </details>
        </div>
    </div>
</header>
