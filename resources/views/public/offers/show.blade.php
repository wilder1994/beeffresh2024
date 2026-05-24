@extends('layouts.store')

@section('titulo', $offer->name.' | BEEF FRESH')

@section('content')
<div class="bf-store-page bf-store-page--medium">
    <p class="mb-4"><a href="{{ route('home') }}" class="text-[var(--bf-brand)] hover:underline">← Volver al inicio</a></p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <img src="{{ $offer->imageUrl() }}" alt="{{ $offer->name }}" class="w-full rounded-2xl object-cover ring-1 ring-black/5">

        <div class="space-y-4">
            <span class="inline-flex text-xs font-semibold uppercase tracking-wide text-[var(--bf-brand)]">Pack</span>
            <h1 class="font-brand text-2xl md:text-3xl text-[var(--bf-ink)]">{{ $offer->name }}</h1>

            @if($availabilityLabel)
                <p class="text-sm font-semibold text-[var(--bf-crimson)]">{{ $availabilityLabel }}</p>
            @endif

            @if($offer->description)
                <p class="text-sm text-[var(--bf-muted)] leading-relaxed">{{ $offer->description }}</p>
            @endif

            <div class="rounded-xl border border-[var(--bf-border-brand-subtle)] bg-white/80 p-4 space-y-2">
                <p class="text-xs uppercase tracking-wide text-[var(--bf-muted)]">Incluye</p>
                <ul class="text-sm space-y-1 text-[var(--bf-ink)]">
                    @foreach($offer->items as $item)
                        <li>{{ number_format((float) $item->quantity, 1, ',', '.') }} {{ $item->sale_unit?->value }} · {{ $item->product?->name }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="flex items-end gap-3">
                @if($referenceTotal > $offerTotal)
                    <p class="text-lg line-through text-gray-400 tabular-nums">${{ number_format($referenceTotal, 0, ',', '.') }}</p>
                @endif
                <p class="text-2xl font-bold text-[var(--bf-brand)] tabular-nums">${{ number_format($offerTotal, 0, ',', '.') }}</p>
            </div>

            @auth
                <form method="POST" action="{{ route('carrito.agregar-offer') }}" class="pt-2">
                    @csrf
                    <input type="hidden" name="offer_id" value="{{ $offer->id }}">
                    <input type="hidden" name="cantidad" value="1">
                    <button type="submit" class="bf-btn-primary w-full justify-center">Agregar pack al carrito</button>
                </form>
            @else
                <p class="text-sm text-[var(--bf-muted)]">
                    <a href="{{ route('login', ['tipo' => 'cliente']) }}" class="text-[var(--bf-brand)] underline">Inicia sesión</a> para comprar este pack.
                </p>
            @endauth
        </div>
    </div>
</div>
@endsection
