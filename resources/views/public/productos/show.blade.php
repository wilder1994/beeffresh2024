@extends('layouts.store')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8">
        <p class="mb-4">
            <a href="{{ route('productos.publico.index') }}" class="text-red-600 hover:underline">← Volver al catálogo</a>
        </p>

        <h2 class="text-2xl font-bold mb-4">{{ $producto->nombre }}</h2>

        @if($producto->imagen)
            <img src="{{ asset('storage/imagenes/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="w-full max-w-md rounded-xl shadow mb-6 object-cover">
        @endif

        <p class="mb-2 text-lg font-semibold text-red-600">
            ${{ number_format((float) $producto->precio, 0, ',', '.') }} / {{ $producto->unidad }}
        </p>

        @if($producto->descripcion)
            <p class="mb-6 text-gray-700">{{ $producto->descripcion }}</p>
        @endif

        @auth
            <form method="POST" action="{{ route('carrito.agregar') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                <div class="flex items-center gap-4">
                    <label class="text-sm font-medium">Cantidad</label>
                    <input type="number" name="cantidad" value="1" min="1" class="bf-input w-24 max-w-[7rem]" required>
                </div>
                <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white border-0">Agregar al carrito</button>
            </form>
            <p class="mt-4 text-sm text-gray-500">En el catálogo también puedes usar el botón rápido para agregar con cantidad 1.</p>
        @else
            <p class="mb-4">
                <a href="{{ route('login', ['tipo' => 'cliente']) }}" class="text-blue-600 underline font-medium">Inicia sesión como cliente</a>
                o
                <a href="{{ route('home', ['registro' => 'confirm']) }}" class="text-blue-600 underline font-medium">regístrate</a>
                para agregar productos al carrito y pagar.
            </p>
        @endauth
    </div>
@endsection
