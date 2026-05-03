@php
    use App\Models\Logo;
    $logoPrincipal = Logo::where('tipo', 'principal')->first();
    $logoPrincipalSrc = ($logoPrincipal && $logoPrincipal->imagen)
        ? asset('storage/logos/'.$logoPrincipal->imagen)
        : asset('logos/logo.jpeg');
    $inicioHref = auth()->check() ? route('dashboard') : route('home');
@endphp

{{-- Barra superior para invitados y clientes (perfil). Personal interno usa sidebar. --}}
<div class="navbar bg-[var(--bf-red)] shadow-lg text-white px-2 md:px-4 min-h-[4rem] flex-wrap md:flex-nowrap gap-y-2 border-b border-black/10">

    <div class="avatar mr-2 shrink-0">
        <a href="{{ $inicioHref }}" class="w-10 rounded-full block overflow-hidden" title="BEEF FRESH">
            <img src="{{ $logoPrincipalSrc }}" alt="Logo BEEF FRESH" class="w-10 h-10 object-cover">
        </a>
    </div>

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
                <li><a href="{{ route('nosotros') }}">Nosotros</a></li>
            </ul>
        </div>
    </div>

    <div class="hidden md:flex flex-1 flex-wrap items-center gap-x-2">
        <a href="{{ $inicioHref }}" class="btn btn-ghost btn-sm text-white">Inicio</a>
        <a href="{{ route('home') }}" class="btn btn-ghost btn-sm text-white">Servicios</a>
        <a href="{{ route('productos.publico.index') }}" class="btn btn-ghost btn-sm text-white">Productos</a>
        <a href="{{ route('nosotros') }}" class="btn btn-ghost btn-sm text-white">Nosotros</a>
    </div>

    @auth
        <div class="flex items-center space-x-2 ml-auto shrink-0">
            <span class="text-xs md:text-sm font-semibold max-w-[140px] md:max-w-none truncate">Hola, {{ auth()->user()->name }}</span>

            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar p-0 border-0 overflow-hidden">
                    <x-user-avatar :user="auth()->user()" size="h-10 w-10" />
                </div>
                <ul class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-white text-gray-900 rounded-box w-52">
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
            <a href="{{ route('login') }}" class="btn btn-outline btn-sm bg-white/95 text-[var(--bf-rust)] border-0 hover:bg-[var(--bf-cream)]">Ingresar</a>
            <a href="{{ route('register') }}" class="btn btn-outline btn-sm bg-[var(--bf-gold)] text-[var(--bf-rust-deep)] border-0 hover:brightness-105">Registrarse</a>
        </div>
    @endauth
</div>
