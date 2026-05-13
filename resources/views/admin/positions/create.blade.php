@extends('layouts.app')

@section('titulo', 'Nuevo cargo')
@section('cabecera', 'Crear cargo')

@section('contenido')
    <div class="max-w-xl mx-auto px-3 sm:px-4 py-4">
        <form method="post" action="{{ route('admin.positions.store') }}" class="bf-form-panel bf-form-panel-tight space-y-4">
            @csrf
            @include('admin.positions._form', ['position' => null])
            <div class="bf-form-actions">
                <button type="submit" class="bf-btn-primary">Guardar</button>
                <a href="{{ route('admin.positions.index') }}" class="bf-btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
