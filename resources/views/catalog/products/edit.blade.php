@extends('catalog.layout')

@section('catalogTitle', 'Editar producto · Catálogo')

@section('catalog')
    <h1 class="text-xl font-bold text-gray-900 mb-4">Editar producto</h1>
    @include('catalog.products._form', [
        'action' => route('catalog.products.update', $product),
        'method' => 'PUT',
        'submitLabel' => 'Guardar cambios',
        'product' => $product,
    ])
@endsection
