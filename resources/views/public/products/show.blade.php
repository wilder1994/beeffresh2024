@extends('layouts.store')

@section('titulo', $product->name.' | BEEF FRESH')

@section('content')
    <div class="bf-store-page bf-store-page--medium bf-store-product-detail">
        <p class="mb-4">
            <a href="{{ route('products.public.index') }}" class="text-[var(--bf-brand)] hover:underline text-sm font-medium">← Volver al catálogo</a>
        </p>

        <div class="bf-store-product-detail__grid">
            <div class="bf-store-product-detail__media">
                @if($product->imageUrl())
                    <div class="bf-store-product-detail__img-wrap">
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="bf-store-product-detail__img" loading="lazy">
                    </div>
                @else
                    <div class="bf-store-product-detail__img-wrap bf-store-product-detail__img-wrap--placeholder">
                        <img src="{{ asset('logos/logo.jpeg') }}" alt="" class="bf-store-product-detail__img opacity-80" loading="lazy">
                    </div>
                @endif
            </div>

            <div class="bf-store-product-detail__panel min-w-0">
                <p class="text-xs text-[var(--bf-muted)] mb-1">{{ $product->meatType?->name }} · {{ $product->meatCut?->name }}</p>
                <h1 class="font-brand text-xl md:text-2xl text-[var(--bf-ink)] leading-tight mb-3">{{ $product->name }}</h1>

                @if($product->description)
                    <p class="mb-4 text-sm text-[var(--bf-muted)] leading-relaxed line-clamp-6 md:line-clamp-none">{{ $product->description }}</p>
                @endif

                <div class="rounded-2xl border border-[var(--bf-border-brand-subtle)] bg-white/90 p-4 md:p-5 shadow-sm" data-store-product-id="{{ $product->id }}">
                    @if($product->isPurchasable())
                        <x-store.product-purchase :product="$product" :can-add="auth()->check()" />

                        @guest
                            <p class="mt-4 text-sm text-[var(--bf-muted)]">
                                <a href="{{ route('login', ['tipo' => 'cliente']) }}" class="text-[var(--bf-brand)] underline font-medium">Inicia sesión como cliente</a>
                                o
                                <a href="{{ route('home', ['registro' => 'confirm']) }}" class="text-[var(--bf-brand)] underline font-medium">regístrate</a>
                                para agregar al carrito.
                            </p>
                        @endguest
                    @else
                        <p class="text-sm font-medium text-[var(--bf-crimson)]" data-store-unavailable-msg data-store-availability-label>
                            Producto no disponible en este momento.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
