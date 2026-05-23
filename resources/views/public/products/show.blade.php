@extends('layouts.store')

@section('content')
    <div class="bf-store-page bf-store-page--narrow">
        <p class="mb-4">
            <a href="{{ route('products.public.index') }}" class="text-red-600 hover:underline">← Volver al catálogo</a>
        </p>

        <p class="text-sm text-gray-500 mb-1">{{ $product->meatType?->name }} · {{ $product->meatCut?->name }}</p>
        <h2 class="text-2xl font-bold mb-4">{{ $product->name }}</h2>

        @if($product->imageUrl())
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="w-full max-w-md rounded-xl shadow mb-6 object-cover">
        @endif

        @if($product->description)
            <p class="mb-6 text-gray-700">{{ $product->description }}</p>
        @endif

        @auth
            @if($product->isPurchasable())
                <x-store.product-purchase :product="$product" />
            @else
                <p class="text-gray-600">Producto no disponible en este momento.</p>
            @endif
        @else
            <p class="mb-4">
                <a href="{{ route('login', ['tipo' => 'cliente']) }}" class="text-blue-600 underline font-medium">Inicia sesión como cliente</a>
                o
                <a href="{{ route('home', ['registro' => 'confirm']) }}" class="text-blue-600 underline font-medium">regístrate</a>
                para agregar productos al carrito.
            </p>
        @endauth
    </div>
@endsection
