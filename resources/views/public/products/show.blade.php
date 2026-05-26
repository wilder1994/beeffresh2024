@extends('layouts.store')

@section('content')
    <div class="bf-store-page bf-store-page--narrow">
        <p class="mb-4">
            <a href="{{ route('products.public.index') }}" class="text-[var(--bf-brand)] hover:underline">← Volver al catálogo</a>
        </p>

        <p class="text-sm text-[var(--bf-muted)] mb-1">{{ $product->meatType?->name }} · {{ $product->meatCut?->name }}</p>
        <h2 class="font-brand text-2xl md:text-3xl text-[var(--bf-ink)] mb-4">{{ $product->name }}</h2>

        @if($product->imageUrl())
            <div class="w-full max-w-md aspect-[4/3] overflow-hidden rounded-xl shadow mb-6 ring-1 ring-black/5 bg-stone-100">
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover object-center">
            </div>
        @endif

        @if($product->description)
            <p class="mb-6 text-[var(--bf-muted)] leading-relaxed">{{ $product->description }}</p>
        @endif

        <div data-store-product-id="{{ $product->id }}">
        @if($product->isPurchasable())
            <x-store.product-purchase :product="$product" :can-add="auth()->check()" />

            @guest
                <p class="mt-4 text-sm text-[var(--bf-muted)]">
                    <a href="{{ route('login', ['tipo' => 'cliente']) }}" class="text-[var(--bf-brand)] underline font-medium">Inicia sesión como cliente</a>
                    o
                    <a href="{{ route('home', ['registro' => 'confirm']) }}" class="text-[var(--bf-brand)] underline font-medium">regístrate</a>
                    para agregar productos al carrito.
                </p>
            @endguest
        @else
            <p class="text-[var(--bf-muted)]" data-store-unavailable-msg data-store-availability-label>Producto no disponible en este momento.</p>
        @endif
        </div>
    </div>
@endsection
