@php
    use App\Models\Logo;
use App\Services\Catalog\CartSessionService;
use App\Services\Catalog\CartStorage;
    $logoPrincipal = Logo::where('tipo', 'principal')->first();
    $logoPrincipalSrc = ($logoPrincipal && $logoPrincipal->imagen)
        ? asset('storage/logos/'.$logoPrincipal->imagen)
        : asset('logos/logo.jpeg');
    $cartCount = (int) round(app(CartSessionService::class)->totalItemCount(app(CartStorage::class)->get()));
@endphp

{{-- Tienda: logo empresa (izq.) · cliente/proveedor con avatar perfil (der.) --}}
<header class="sticky top-0 z-50 overflow-visible shadow-md border-b border-black/10 bg-[var(--bf-brand)] text-white">
    <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3 overflow-visible">
        <a href="{{ route('home') }}" class="flex items-center gap-3 group shrink-0" title="BEEF FRESH · Inicio">
            <img src="{{ $logoPrincipalSrc }}" alt="BEEF FRESH" class="h-11 w-11 rounded-full object-cover ring-2 ring-white/30">
            <div class="leading-tight hidden sm:block">
                <span class="font-brand tracking-tight text-lg">BEEF FRESH</span>
                <span class="block text-[11px] uppercase tracking-widest text-white/75">Carnes frescas · Tienda en línea</span>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-1 text-sm font-medium order-3 md:order-none flex-1 md:flex-none md:justify-center">
            <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('home') ? 'bg-white/15' : '' }}">Inicio</a>
            <a href="{{ route('nosotros') }}" class="px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('nosotros') ? 'bg-white/15' : '' }}">Nosotros</a>
            <a href="{{ route('products.public.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('products.public.*') ? 'bg-white/15' : '' }}">Catálogo</a>
            <x-store.cart-nav-link :count="$cartCount" class="px-3 py-2 hidden md:inline-flex" />
        </nav>

        <div class="flex items-center gap-2 shrink-0 ml-auto overflow-visible">
            <x-store.cart-nav-link
                :count="$cartCount"
                class="btn btn-ghost btn-circle text-white border border-white/30 md:hidden"
            />

            @auth
                <x-notifications.bell variant="dark" />
            @endauth

            @auth
                @php($navUser = auth()->user())

                @if($navUser->isStaff())
                    <a href="{{ route('dashboard') }}" class="text-xs px-3 py-2 rounded-lg bg-[var(--bf-gold)] text-[var(--bf-rust-deep)] font-semibold hover:brightness-110">
                        Gestión interna
                    </a>
                    <details class="relative">
                        <summary class="btn btn-ghost btn-sm text-white border border-white/30 list-none [&::-webkit-details-marker]:hidden cursor-pointer">{{ $navUser->name }}</summary>
                        <ul class="menu menu-sm absolute right-0 top-full z-[200] mt-2 w-52 rounded-box border border-black/10 bg-white p-2 text-gray-900 shadow-lg">
                            <li><x-profile.open-button tag="a" class="block w-full rounded-lg px-3 py-2 hover:bg-stone-100 font-normal">Mi perfil</x-profile.open-button></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left rounded-lg px-3 py-2 hover:bg-stone-100">Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </details>
                @elseif($navUser->isCustomer() || $navUser->isSupplier())
                    @if($navUser->isSupplier())
                        <a href="{{ route('supplier.home') }}" class="hidden sm:inline-flex text-xs px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 font-medium">
                            Portal proveedor
                        </a>
                    @endif
                    <x-nav-user-menu :user="$navUser" variant="dark" />
                @endif
            @else
                <x-auth.login-dropdown variant="store" />
                <x-auth.register-button variant="store" />
            @endauth

            <details class="relative z-[60] md:hidden">
                <summary class="btn btn-ghost btn-circle text-white border border-white/30 list-none [&::-webkit-details-marker]:hidden cursor-pointer" aria-label="Menú">☰</summary>
                <ul class="menu menu-sm absolute right-0 top-full z-[200] mt-2 w-52 rounded-box border border-black/10 bg-white p-2 text-gray-900 shadow-lg">
                    <li><a href="{{ route('home') }}">Inicio</a></li>
                    <li><a href="{{ route('nosotros') }}">Nosotros</a></li>
                    <li><a href="{{ route('products.public.index') }}">Catálogo</a></li>
                    <li><a href="{{ route('carrito.ver') }}">Carrito<span data-bf-cart-menu-count>{{ $cartCount > 0 ? ' ('.$cartCount.')' : '' }}</span></a></li>
                    @guest
                        <li><a href="{{ route('login', ['tipo' => 'cliente']) }}">Ingresar — cliente</a></li>
                        <li><a href="{{ route('login', ['tipo' => 'empleado']) }}">Ingresar — empleado</a></li>
                        <li><a href="{{ route('login', ['tipo' => 'proveedor']) }}">Ingresar — proveedor</a></li>
                        <li>
                            <button
                                type="button"
                                class="w-full text-left rounded-lg px-3 py-2 hover:bg-stone-100 font-normal"
                                x-on:click.prevent="window.bfOpenRegisterConfirm && window.bfOpenRegisterConfirm()"
                                onclick="event.preventDefault(); window.bfOpenRegisterConfirm && window.bfOpenRegisterConfirm();"
                            >
                                Registrarse
                            </button>
                        </li>
                    @endguest
                    @auth
                        @if(auth()->user()->isSupplier())
                            <li><a href="{{ route('supplier.home') }}">Portal proveedor</a></li>
                        @endif
                        <li><x-profile.open-button tag="a" class="block w-full rounded-lg px-3 py-2 hover:bg-stone-100">Mi perfil</x-profile.open-button></li>
                        @if(auth()->user()->isCustomer())
                            <li><a href="{{ route('customer.orders.index') }}">Mis pedidos</a></li>
                        @endif
                    @endauth
                </ul>
            </details>
        </div>
    </div>
</header>
