@extends('catalog.layout')

@section('catalogTitle', 'Nuevo producto · Catálogo')

@section('catalog')
    <h1 class="text-xl font-bold text-gray-900 mb-4">Nuevo producto</h1>
    @include('catalog.products._form', [
        'action' => route('catalog.products.store'),
        'submitLabel' => 'Crear producto',
    ])
@endsection
