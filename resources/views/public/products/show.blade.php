@extends('layouts.store')

@section('content')
    <div class="bf-store-page bf-store-page--narrow">
        <p class="mb-4">
            <a href="{{ route('products.public.index') }}" class="text-[var(--bf-brand)] hover:underline">← Volver al catálogo</a>
        </p>

        <p class="text-sm text-[var(--bf-muted)] mb-1">{{ $product->meatType?->name }} · {{ $product->meatCut?->name }}</p>
        <h2 class="font-brand text-2xl md:text-3xl text-[var(--bf-ink)] mb-4">{{ $product->name }}</h2>

        @if($product->imageUrl())
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="w-full max-w-md rounded-xl shadow mb-6 object-cover ring-1 ring-black/5">
        @endif

        @if($product->description)
            <p class="mb-6 text-[var(--bf-muted)] leading-relaxed">{{ $product->description }}</p>
        @endif

        @if($volumeOfferView)
            @php
                $unit = $volumeOfferView['unit'];
                $minQty = $volumeOfferView['min_qty'];
                $minDisplay = fmod($minQty, 1.0) === 0.0 ? (string) (int) $minQty : number_format($minQty, 1, ',', '.');
            @endphp
            <div class="mb-6 rounded-xl border border-[var(--bf-border-brand-subtle)] bg-white/80 p-4 space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--bf-brand)]">Oferta por cantidad</p>
                <p class="text-sm text-[var(--bf-ink)]">
                    Compra {{ $minDisplay }} {{ $unit->value }} o más y paga
                    <strong class="text-[var(--bf-brand)]">${{ number_format($volumeOfferView['offer_unit_price'], 0, ',', '.') }}/{{ $unit->value }}</strong>
                    <span class="line-through text-gray-400">${{ number_format($volumeOfferView['reference_unit_price'], 0, ',', '.') }}</span>
                </p>
                @if($volumeOfferView['label'])
                    <p class="text-xs font-semibold text-[var(--bf-crimson)]">{{ $volumeOfferView['label'] }}</p>
                @endif
            </div>
        @endif

        @auth
            @if($product->isPurchasable())
                <x-store.product-purchase :product="$product" />
            @else
                <p class="text-[var(--bf-muted)]">Producto no disponible en este momento.</p>
            @endif
        @else
            <p class="mb-4 text-sm text-[var(--bf-muted)]">
                <a href="{{ route('login', ['tipo' => 'cliente']) }}" class="text-[var(--bf-brand)] underline font-medium">Inicia sesión como cliente</a>
                o
                <a href="{{ route('home', ['registro' => 'confirm']) }}" class="text-[var(--bf-brand)] underline font-medium">regístrate</a>
                para agregar productos al carrito.
            </p>
        @endauth
    </div>
@endsection
