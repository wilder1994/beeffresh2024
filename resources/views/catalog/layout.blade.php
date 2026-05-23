@extends('layouts.app')

@section('titulo', $catalogTitle ?? 'Catálogo')

@section('contenido')
    @include('catalog.partials.tabs')

    <div class="max-w-7xl mx-auto px-2 sm:px-3 md:px-4 pb-8">
        @yield('catalog')
    </div>
@endsection
