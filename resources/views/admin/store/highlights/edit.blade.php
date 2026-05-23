@extends('layouts.app')

@section('titulo', 'Editar destacado')
@section('cabecera', 'Editar destacado')

@section('contenido')
    <div class="max-w-xl mx-auto px-3 py-4">
        <form action="{{ route('admin.store.highlights.update', $highlight) }}" method="POST" enctype="multipart/form-data" class="bf-form-panel space-y-3">
            @csrf @method('PUT')
            <div><label class="bf-label">Título</label><input type="text" name="title" class="bf-input" value="{{ old('title', $highlight->title) }}" required></div>
            <div><label class="bf-label">Descripción</label><textarea name="description" class="bf-textarea" rows="3">{{ old('description', $highlight->description) }}</textarea></div>
            <div><label class="bf-label">Imagen</label><input type="file" name="image" class="bf-file" accept="image/*">@if($highlight->imageUrl())<img src="{{ $highlight->imageUrl() }}" class="mt-2 w-28 h-28 object-cover rounded-lg">@endif</div>
            <div><label class="bf-label">Orden</label><input type="number" name="sort_order" class="bf-input" min="0" value="{{ old('sort_order', $highlight->sort_order) }}"></div>
            <label class="bf-form-check-item"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $highlight->is_active))> Activo</label>
            <div class="bf-form-actions"><a href="{{ route('admin.store.highlights.index') }}" class="bf-btn-ghost">Cancelar</a><button class="bf-btn-primary">Guardar</button></div>
        </form>
    </div>
@endsection
