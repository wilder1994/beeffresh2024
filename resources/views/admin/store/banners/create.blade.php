@extends('layouts.app')

@section('titulo', 'Nuevo banner')
@section('cabecera', 'Nuevo banner')

@section('contenido')
    <div class="max-w-3xl mx-auto px-3 py-4">
        <form action="{{ route('admin.store.banners.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel space-y-3">
            @csrf

            @include('admin.store.banners._form-fields')

            <div class="bf-form-actions">
                <a href="{{ route('admin.store.banners.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection
