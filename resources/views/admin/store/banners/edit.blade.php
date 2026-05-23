@extends('layouts.app')

@section('titulo', 'Editar banner')
@section('cabecera', 'Editar banner')

@section('contenido')
    <div class="max-w-3xl mx-auto px-3 py-4">
        <form action="{{ route('admin.store.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="bf-form-panel space-y-3">
            @csrf
            @method('PUT')

            @include('admin.store.banners._form-fields', ['banner' => $banner])

            <div class="bf-form-actions">
                <a href="{{ route('admin.store.banners.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection
