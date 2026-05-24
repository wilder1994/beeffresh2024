@extends('catalog.layout')

@section('catalogTitle', 'Nueva oferta · Catálogo')

@section('catalog')
    <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900">Nueva oferta</h1>
        <p class="text-sm text-gray-600">Pack de varios productos u oferta por cantidad mínima.</p>
    </div>

    <form action="{{ route('catalog.offers.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel space-y-4">
        @csrf
        @include('catalog.offers._form', ['products' => $products])
        <div class="bf-form-actions">
            <button type="submit" class="bf-btn-primary">Guardar</button>
            <a href="{{ route('catalog.offers.index') }}" class="bf-btn-ghost">Cancelar</a>
        </div>
    </form>
@endsection
