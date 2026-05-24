@extends('catalog.layout')

@section('catalogTitle', ($pageTitle ?? 'Nueva oferta').' · Catálogo')

@section('catalog')
    <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900">{{ $pageTitle ?? 'Nueva oferta' }}</h1>
        <p class="text-sm text-gray-600">{{ $pageDescription ?? 'Pack de varios productos u oferta por cantidad mínima.' }}</p>
    </div>

    <form action="{{ route('catalog.offers.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel space-y-4">
        @csrf
        @include('catalog.offers._form', [
            'products' => $products,
            'defaultType' => $defaultType,
            'lockType' => true,
        ])
        <div class="bf-form-actions">
            <button type="submit" class="bf-btn-primary">Guardar</button>
            <a href="{{ $cancelUrl }}" class="bf-btn-ghost">Cancelar</a>
        </div>
    </form>
@endsection
