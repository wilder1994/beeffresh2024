@extends('layouts.app')

@section('titulo', 'Nuevo destacado')
@section('cabecera', 'Nuevo destacado')

@section('contenido')
    <div class="max-w-xl mx-auto px-3 py-4">
        <form action="{{ route('admin.store.highlights.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel space-y-3">
            @csrf
            <div><label class="bf-label">Título</label><input type="text" name="title" class="bf-input" required></div>
            <div><label class="bf-label">Descripción</label><textarea name="description" class="bf-textarea" rows="3"></textarea></div>
            <div><label class="bf-label">Imagen</label><input type="file" name="image" class="bf-file" accept="image/*"></div>
            <div><label class="bf-label">Orden</label><input type="number" name="sort_order" class="bf-input" min="0" value="0"></div>
            <label class="bf-form-check-item"><input type="checkbox" name="is_active" value="1" checked> Activo</label>
            <div class="bf-form-actions"><a href="{{ route('admin.store.highlights.index') }}" class="bf-btn-ghost">Cancelar</a><button class="bf-btn-primary">Guardar</button></div>
        </form>
    </div>
@endsection
