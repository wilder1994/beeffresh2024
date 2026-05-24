@extends('layouts.store')

@section('titulo', 'Catálogo | BEEF FRESH')

@section('content')
@php use Illuminate\Support\Str; @endphp
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

    <form method="GET" action="{{ route('products.public.index') }}" class="mb-6 flex flex-col md:flex-row items-center gap-4">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            class="w-full md:w-1/2 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
            placeholder="Buscar productos…">

        <select name="meat_type_id" class="w-full md:w-1/4 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            <option value="">Todos los tipos</option>
            @foreach ($meatTypes as $type)
                <option value="{{ $type->id }}" @selected((int) request('meat_type_id') === $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>

        @if(request('meat_cut_id'))
            <input type="hidden" name="meat_cut_id" value="{{ request('meat_cut_id') }}">
        @endif

        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-lg transition duration-200">
            Buscar
        </button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition duration-300 flex flex-col overflow-hidden">
                @if($product->imageUrl())
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="w-full h-40 object-cover">
                @else
                    <div class="w-full h-40 bg-stone-100 flex items-center justify-center text-stone-400 text-sm">Sin imagen</div>
                @endif

                <div class="p-4 flex flex-col flex-1">
                    <p class="text-xs text-gray-500">{{ $product->meatType?->name }} · {{ $product->meatCut?->name }}</p>
                    <h3 class="text-lg font-semibold text-gray-800">
                        <a href="{{ route('products.public.show', $product) }}" class="hover:text-red-600 hover:underline">{{ $product->name }}</a>
                    </h3>
                    @if($product->description)
                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($product->description, 80) }}</p>
                    @endif

                    @if($product->isPurchasable())
                        <x-store.product-purchase :product="$product" />
                    @else
                        <p class="text-sm text-gray-500 mt-2">No disponible</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-600">No se encontraron productos.</div>
        @endforelse
    </div>
</div>
@endsection
