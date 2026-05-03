<footer class="mt-auto border-t border-[var(--bf-red)]/15 bg-white/90 backdrop-blur">
    <div class="max-w-6xl mx-auto px-4 py-8 text-sm text-gray-700">
        <div class="flex flex-wrap justify-center gap-8 mb-4">
            <a href="{{ route('home') }}" class="hover:text-[var(--bf-red)]">Inicio</a>
            <a href="{{ route('productos.publico.index') }}" class="hover:text-[var(--bf-red)]">Catálogo</a>
            @auth
                <a href="{{ route('carrito.ver') }}" class="hover:text-[var(--bf-red)]">Carrito</a>
            @endauth
        </div>
        <p class="text-center text-gray-500">&copy; {{ date('Y') }} BEEF FRESH · Carnicería</p>
    </div>
</footer>
