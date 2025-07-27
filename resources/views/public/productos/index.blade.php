@extends('layouts.public')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Búsqueda --}}
    <form method="GET" action="{{ route('productos.publico.index') }}" class="mb-6 flex flex-col md:flex-row items-center gap-4">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            class="w-full md:w-1/2 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
            placeholder="Buscar productos...">

        @if(request('buscar'))
            <select name="categoria" class="w-full md:w-1/4 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ request('categoria') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
        @endif

        <button type="submit"
            class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-lg transition duration-200">
            🔍 Buscar
        </button>
    </form>

    {{-- Productos --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($productos as $producto)
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition duration-300 flex flex-col overflow-hidden">
                <img src="{{ asset('storage/imagenes/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">

                <div class="p-4 flex flex-col justify-between flex-1">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $producto->nombre }}</h3>

                        {{-- Descripción --}}
                        @if($producto->descripcion)
                            <p class="text-sm text-gray-600 mt-1">{{ $producto->descripcion }}</p>
                        @endif

                       {{-- Precio y Promoción --}}
                        <div class="mt-2">
                            @if($producto->promocion)
                                {{-- Texto de promoción plano --}}
                                <p class="text-sm text-green-600 font-semibold">🔥 {{ $producto->promocion }}</p>
                            @endif

                            {{-- Precio siempre visible --}}
                            <p class="text-red-600 text-base font-medium">
                                ${{ number_format($producto->precio, 0, ',', '.') }} / {{ $producto->unidad }}
                            </p>
                        </div>



                    </div>

                    <button 
                        class="agregar-carrito bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition mt-3"
                        data-id="{{ $producto->id }}"
                    >
                        Agregar al carrito
                    </button>
                </div>
            </div>

        @empty
            <div class="col-span-3 text-center text-gray-600">
                No se encontraron productos.
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const botones = document.querySelectorAll('.agregar-carrito');
    const iconoCarrito = document.querySelector('a[href="{{ route("carrito.ver") }}"]');

    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            const id = boton.dataset.id;

            // Crear animación tipo "piedrita"
            const efecto = document.createElement('div');
            efecto.className = 'fixed z-50 w-4 h-4 bg-red-600 rounded-full';
            const rect = boton.getBoundingClientRect();
            efecto.style.top = rect.top + 'px';
            efecto.style.left = rect.left + 'px';
            document.body.appendChild(efecto);

            const destino = iconoCarrito.getBoundingClientRect();
            efecto.animate([
                { transform: 'translate(0, 0)', opacity: 1 },
                { transform: `translate(${destino.left - rect.left}px, ${destino.top - rect.top}px)`, opacity: 0 }
            ], {
                duration: 800,
                easing: 'ease-in-out'
            }).onfinish = () => efecto.remove();

            // Enviar AJAX
            fetch("{{ route('carrito.agregar') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ producto_id: id, cantidad: 1 })
            })
            .then(res => res.json())
            .then(data => {
                const badge = iconoCarrito.querySelector('span');
                if (badge) {
                    badge.textContent = data.totalProductos;
                } else {
                    const nuevoBadge = document.createElement('span');
                    nuevoBadge.className = "absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center";
                    nuevoBadge.textContent = data.totalProductos;
                    iconoCarrito.appendChild(nuevoBadge);
                }
            });
        });
    });
});
</script>
@endpush
