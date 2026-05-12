@extends('layouts.app')

@section('titulo', 'Nuevo usuario')
@section('cabecera', 'Crear usuario')

@section('contenido')
    <div class="py-4 md:py-6 max-w-4xl mx-auto px-3 sm:px-4">
        <form method="post" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-4">
            @csrf
            @include('admin.users._form', ['user' => null, 'roles' => $roles])
            <div class="bf-form-actions">
                <button type="submit" class="bf-btn-primary">Guardar</button>
                <a href="{{ route('admin.users.index') }}" class="bf-btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
