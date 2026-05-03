@extends('layouts.store')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold mb-6 text-center">🛒 Tu carrito</h2>
        <div class="mb-6 text-center">
            <a href="{{ url('/productos-publicos') }}" class="inline-block bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded">
              ← Regresar a productos
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @forelse($carrito as $item)
            <div class="flex items-center gap-4 mb-4 border rounded p-4 shadow-sm">
                <img src="{{ asset('storage/imagenes/' . $item['imagen']) }}"
                    alt="{{ $item['nombre'] }}"
                    class="w-20 h-20 object-cover rounded"
                    onerror="this.src='{{ asset('images/sin-imagen.png') }}'">

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
            <div class="mt-8 text-center space-y-4">
                @guest
                    <p class="text-gray-700 mb-2">Para pagar debes tener una cuenta.</p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary">Ingresar</a>
                        <a href="{{ route('register') }}" class="btn btn-outline">Registrarse</a>
                    </div>
                @else
                    <a href="{{ route('checkout.show') }}" class="btn btn-primary btn-lg">
                        Continuar al pago
                    </a>
                    <p class="text-sm text-gray-500 mt-2">Serás redirigido al resumen y confirmación de compra.</p>
                @endguest
            </div>
        @endif
    </div>
@endsection
