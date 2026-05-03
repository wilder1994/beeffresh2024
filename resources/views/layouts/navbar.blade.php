@php
    use App\Models\Logo;
    $logoPrincipal = Logo::where('tipo', 'principal')->first();
    $logoAdmin = Logo::where('tipo', 'administrador')->first();
    $logoPrincipalSrc = ($logoPrincipal && $logoPrincipal->imagen)
        ? asset('storage/logos/'.$logoPrincipal->imagen)
        : asset('logos/logo.jpeg');
    $isStaff = auth()->check() && auth()->user()->isStaff();
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $inicioHref = auth()->check() ? route('dashboard') : route('home');
@endphp

<div class="navbar bg-[#7c2d12] shadow-lg text-white px-2 md:px-4 min-h-[4rem] flex-wrap md:flex-nowrap gap-y-2">

    <div class="avatar mr-2 shrink-0">
        <a href="{{ $inicioHref }}" class="w-10 rounded-full block overflow-hidden" title="BEEF FRESH">
            <img src="{{ $logoPrincipalSrc }}" alt="Logo BEEF FRESH" class="w-10 h-10 object-cover">
        </a>
    </div>

    @if($isStaff)
        {{-- Personal interno: una sola barra marrón --}}
        <div class="flex-none md:hidden w-full order-last md:order-none">
            <div class="dropdown w-full">
                <label tabindex="0" class="btn btn-ghost btn-sm text-white border border-white/30 w-full justify-between">
                    Menú
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </label>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-[#fffaf5] text-gray-900 rounded-box w-full max-h-[70vh] overflow-y-auto z-[60] mt-1 border border-amber-100">
                    <li><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="menu-title text-xs text-gray-500 px-2 py-0">Vista clientes</li>
                    <li><a href="{{ route('home') }}" target="_blank" rel="noopener">Inicio tienda</a></li>
                    <li><a href="{{ route('productos.publico.index') }}" target="_blank" rel="noopener">Catálogo público</a></li>
                    <li><a href="{{ route('carrito.ver') }}" target="_blank" rel="noopener">Carrito</a></li>
                    @if($isAdmin)
                        <li><a href="{{ route('admin.pedidos.index') }}">Pedidos</a></li>
                        <li class="menu-title text-xs text-gray-500 px-2 py-0">Gestión</li>
                        <li><a href="{{ route('productos.index') }}">Productos</a></li>
                        <li><a href="{{ route('videos.index') }}">Videos</a></li>
                        <li><a href="{{ route('recetas.index') }}">Recetas</a></li>
                        <li><a href="{{ route('promociones.index') }}">Promociones</a></li>
                        <li><a href="{{ route('cortes.index') }}">Cortes</a></li>
                    @endif
                    <li><a href="{{ route('home') }}#nosotros">Nosotros</a></li>
                </ul>
            </div>
        </div>

        <div class="hidden md:flex flex-1 flex-wrap items-center gap-x-1 gap-y-1 text-sm lg:text-base">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm text-white hover:bg-white/10">Inicio</a>

            <div class="dropdown dropdown-hover">
                <label tabindex="0" class="btn btn-ghost btn-sm text-white hover:bg-white/10">Ver tienda (clientes)</label>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-[#fffaf5] text-gray-900 rounded-box w-56 z-[60] border border-amber-100">
                    <li class="menu-title text-xs text-gray-500">Lo que ven los clientes</li>
                    <li><a href="{{ route('home') }}" target="_blank" rel="noopener">Inicio tienda</a></li>
                    <li><a href="{{ route('productos.publico.index') }}" target="_blank" rel="noopener">Catálogo público</a></li>
                    <li><a href="{{ route('carrito.ver') }}" target="_blank" rel="noopener">Carrito</a></li>
                </ul>
            </div>

            @if($isAdmin)
                <a href="{{ route('admin.pedidos.index') }}" class="btn btn-ghost btn-sm text-white hover:bg-white/10">Pedidos</a>
                <a href="{{ route('productos.index') }}" class="btn btn-ghost btn-sm text-white hover:bg-white/10">Productos</a>
                <a href="{{ route('videos.index') }}" class="btn btn-ghost btn-sm text-white hover:bg-white/10">Videos</a>
                <a href="{{ route('recetas.index') }}" class="btn btn-ghost btn-sm text-white hover:bg-white/10">Recetas</a>
                <a href="{{ route('promociones.index') }}" class="btn btn-ghost btn-sm text-white hover:bg-white/10">Promociones</a>
                <a href="{{ route('cortes.index') }}" class="btn btn-ghost btn-sm text-white hover:bg-white/10">Cortes</a>
            @endif

            <a href="{{ route('home') }}#nosotros" class="btn btn-ghost btn-sm text-white hover:bg-white/10">Nosotros</a>
        </div>
    @else
        {{-- Invitados y clientes (tienda / perfil con layout app) --}}
        <div class="flex-1 md:hidden">
            <div class="dropdown dropdown-end">
                <button tabindex="0" role="button" class="btn btn-ghost btn-circle text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </button>
                <ul class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-[#f8fafc] text-gray-800 rounded-box w-52">
                    <li><a href="{{ $inicioHref }}">Inicio</a></li>
                    <li><a href="{{ route('home') }}">Servicios</a></li>
                    <li><a href="{{ route('productos.publico.index') }}">Productos</a></li>
                    <li><a href="{{ route('home') }}#nosotros">Nosotros</a></li>
                </ul>
            </div>
        </div>

        <div class="hidden md:flex flex-1 flex-wrap items-center gap-x-2">
            <a href="{{ $inicioHref }}" class="btn btn-ghost btn-sm text-white">Inicio</a>
            <a href="{{ route('home') }}" class="btn btn-ghost btn-sm text-white">Servicios</a>
            <a href="{{ route('productos.publico.index') }}" class="btn btn-ghost btn-sm text-white">Productos</a>
            <a href="{{ route('home') }}#nosotros" class="btn btn-ghost btn-sm text-white">Nosotros</a>
        </div>
    @endif

    @auth
        <div class="flex items-center space-x-2 ml-auto shrink-0">
            <span class="text-xs md:text-sm font-semibold max-w-[140px] md:max-w-none truncate">Hola, {{ auth()->user()->name }}</span>

            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full">
                        <img alt="avatar" class="w-10 h-10 rounded-full object-cover"
                          src="{{ $logoAdmin && $logoAdmin->imagen ? asset('storage/logos/' . $logoAdmin->imagen) . '?' . time() : 'https://picsum.photos/id/35/50' }}" />
                    </div>
                </div>
                <ul class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-white text-gray-900 rounded-box w-52">
                    @if(auth()->user()->isAdmin())
                        <li><a href="{{ route('admin.logo.edit', 'principal') }}">Editar logos</a></li>
                    @endif
                    <li><a href="{{ route('dashboard') }}">Mi panel</a></li>
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
    @else
        <div class="space-x-2 ml-auto shrink-0">
            <a href="{{ route('login') }}" class="btn btn-outline btn-sm bg-white text-black border-0">Ingresar</a>
            <a href="{{ route('register') }}" class="btn btn-outline btn-sm bg-white text-black border-0">Registrarse</a>
        </div>
    @endauth
</div>
