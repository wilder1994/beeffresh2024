@extends('layouts.store')

@section('titulo', 'Catálogo | BEEF FRESH')

@section('content')
<div class="bf-store-page bf-store-page--wide">
    @if(isset($selectedMeatCut) && $selectedMeatCut)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[var(--bf-border-brand-subtle)] bg-white/80 px-4 py-3">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--bf-brand)]">Corte seleccionado</p>
                <p class="text-base font-semibold text-[var(--bf-ink)]">{{ $selectedMeatCut->name }}</p>
                @if($selectedMeatCut->meatType)
                    <p class="text-sm text-[var(--bf-muted)]">{{ $selectedMeatCut->meatType->name }}</p>
                @endif
            </div>
            <a href="{{ route('products.public.index') }}" class="bf-btn-ghost shrink-0 text-sm">Ver todo el catálogo</a>
        </div>
    @endif

    @if($promoFilter ?? false)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-200/80 bg-amber-50/90 px-4 py-2.5 text-sm text-amber-950">
            <span>Mostrando productos en promoción.</span>
            <a href="{{ route('products.public.index') }}" class="font-semibold text-[var(--bf-brand)] hover:underline">Ver catálogo completo</a>
        </div>
    @endif

    <form method="GET" action="{{ route('products.public.index') }}" class="mb-5 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
        @if($promoFilter ?? false)
            <input type="hidden" name="promo" value="1">
        @endif
        <input type="search" name="buscar" value="{{ request('buscar') }}"
            class="bf-input w-full sm:flex-1 min-w-[12rem]"
            placeholder="Buscar productos…">

        <select name="meat_type_id" class="bf-input w-full sm:w-48">
            <option value="">Todos los tipos</option>
            @foreach ($meatTypes as $type)
                <option value="{{ $type->id }}" @selected((int) request('meat_type_id') === $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>

        @if(request('meat_cut_id'))
            <input type="hidden" name="meat_cut_id" value="{{ request('meat_cut_id') }}">
        @endif

        <button type="submit" class="bf-btn-primary shrink-0">Buscar</button>
    </form>

    @if($catalogRows->isEmpty())
        <p class="bf-ops-empty text-center py-12 text-[var(--bf-muted)]">No se encontraron productos.</p>
    @else
        <div class="bf-home-products__grid">
            @foreach($catalogRows as $row)
                @php
                    $product = $row['product'];
                    $card = $row['card'];
                @endphp
                <x-store.home-product-card
                    :url="route('products.public.show', $product)"
                    :product-id="$product->id"
                    :image-url="$card['image_url']"
                    :title="$product->name"
                    :badge="$card['badge']"
                    :price-label="$card['unit_price']"
                    :reference-price="$card['reference_price']"
                    :availability-label="$card['availability_label']"
                    :meta="$card['meta']"
                />
            @endforeach
        </div>
    @endif
</div>
@endsection
