@extends('layouts.app')

@section('titulo', 'Editar cargo')
@section('cabecera', 'Editar cargo')

@section('contenido')
    <div class="max-w-xl mx-auto px-3 sm:px-4 py-4">
        <form method="post" action="{{ route('admin.positions.update', $position) }}" class="bf-form-panel bf-form-panel-tight space-y-4">
            @csrf
            @method('PUT')
            @include('admin.positions._form', ['position' => $position])
            <div class="bf-form-actions">
                <button type="submit" class="bf-btn-primary">Actualizar</button>
                <a href="{{ route('admin.positions.index') }}" class="bf-btn-ghost">Volver</a>
            </div>
        </form>
    </div>
@endsection
