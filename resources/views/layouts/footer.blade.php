<div class="max-w-6xl mx-auto px-4 py-4 text-sm text-white">
    <nav class="flex flex-wrap justify-center gap-6 mb-2">
        <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="hover:underline opacity-90 hover:opacity-100">Inicio</a>
        <a href="{{ route('productos.publico.index') }}" class="hover:underline opacity-90 hover:opacity-100">Catálogo</a>
        @auth
            <a href="{{ route('carrito.ver') }}" class="hover:underline opacity-90 hover:opacity-100">Carrito</a>
        @endauth
    </nav>
    <p class="opacity-80">&copy; {{ date('Y') }} BEEF FRESH.</p>
</div>
