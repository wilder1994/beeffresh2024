@extends('layouts.public')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold mb-6 text-center">🛒 Tu carrito</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @forelse($carrito as $item)
            <div class="flex items-center gap-4 mb-4 border rounded p-4 shadow-sm">
                <img src="{{ asset('storage/' . $item['imagen']) }}" alt="{{ $item['nombre'] }}" class="w-20 h-20 object-cover rounded">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold">{{ $item['nombre'] }}</h3>
                    <p class="text-gray-600">Precio: ${{ number_format($item['precio'], 0, ',', '.') }}</p>
                    <p class="text-gray-600">Cantidad: {{ $item['cantidad'] }}</p>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-600">No hay productos en tu carrito.</p>
        @endforelse

        @if(count($carrito))
            <div class="mt-6 text-center">
                <button disabled class="bg-gray-400 text-white px-6 py-2 rounded cursor-not-allowed">
                    Pasarela de pago (pendiente)
                </button>
            </div>
        @endif
    </div>
@endsection
