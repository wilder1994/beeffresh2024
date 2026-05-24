@extends('catalog.layout')

@section('catalogTitle', 'Editar oferta · Catálogo')

@section('catalog')
    <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900">Editar oferta</h1>
        <p class="text-sm text-gray-600">{{ $offer->name }}</p>
    </div>

    <form action="{{ route('catalog.offers.update', $offer) }}" method="POST" enctype="multipart/form-data" class="bf-form-panel space-y-4">
        @csrf
        @method('PUT')
        @include('catalog.offers._form', ['offer' => $offer, 'products' => $products])
        <div class="bf-form-actions">
            <button type="submit" class="bf-btn-primary">Actualizar</button>
            <a href="{{ route('catalog.offers.index') }}" class="bf-btn-ghost">Cancelar</a>
        </div>
    </form>
@endsection
