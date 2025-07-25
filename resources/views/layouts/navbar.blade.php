@php
    use App\Models\Logo;
    $logoAdmin = Logo::where('tipo', 'administrador')->first();
@endphp

<div class="navbar bg-[#7c2d12] shadow-lg text-white px-4">
    
    {{-- Logo principal con fallback --}}
    <div class="avatar mr-4">
        <a href="/" class="w-10 rounded-full block overflow-hidden">
            <img src="{{ $logo && $logo->imagen ? asset('storage/logos/' . $logo->imagen) : asset('storage/imagenes/logo.jpeg') }}" alt="Logo BEEF FRESH">
        </a>
    </div>

    {{-- Menú móvil (hamburguesa) --}}
    <div class="flex-1 md:hidden">
        <div class="dropdown">
            <button tabindex="0" role="button" class="btn btn-ghost btn-circle">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </button>
            <ul class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-[#f8fafc] text-gray-800 rounded-box w-52">
                <li><a href="{{ route('home') }}">Inicio</a></li>
                <li><a href="{{ route('home') }}">Servicios</a></li>
                <li><a href="{{ Auth::check() ? route('productos.index') : route('productos.publico.index') }}">Productos</a></li>
                <li><a href="{{ route('home') }}">Nosotros</a></li>
            </ul>
        </div>
    </div>

    {{-- Menú escritorio --}}
    <div class="hidden md:flex flex-1 space-x-4">
        <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Inicio</a>
        <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Servicios</a>
        <a href="{{ Auth::check() ? route('productos.index') : route('productos.publico.index') }}" class="btn btn-ghost btn-sm">Productos</a>
        <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Nosotros</a>
    </div>

    {{-- Usuario autenticado / login --}}
    @auth
        <div class="flex items-center space-x-2">
            <span class="text-sm font-semibold">Hola, {{ auth()->user()->name }}</span>

            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full">
                        <img alt="avatar" class="w-10 h-10 rounded-full"
                          src="{{ $logoAdmin && $logoAdmin->imagen ? asset('storage/logos/' . $logoAdmin->imagen) . '?' . time() : 'https://picsum.photos/id/35/50' }}" />
                    </div>
                </div>
                <ul class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-white text-gray-900 rounded-box w-52">
                    <li><a href="{{ route('admin.logo.edit', 'principal') }}">Editar Logos</a></li>
                    <li><a href="{{ route('dashboard') }}">Panel de Administración</a></li>
                    <li><a href="{{ route('profile.edit') }}">Mi Perfil</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">Cerrar Sesión</a>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    @else
        <div class="space-x-2">
            <a href="{{ route('login') }}" class="btn btn-outline btn-sm bg-white text-black">Ingresar</a>
            <a href="{{ route('register') }}" class="btn btn-outline btn-sm bg-white text-black">Registrarse</a>
        </div>
    @endauth
</div>
