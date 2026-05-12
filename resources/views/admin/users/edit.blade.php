@extends('layouts.app')

@section('titulo', 'Editar usuario')
@section('cabecera', 'Editar usuario')

@section('contenido')
    <div class="py-4 md:py-6 max-w-3xl mx-auto px-3 sm:px-4">
        <form method="post" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-4">
            @csrf
            @method('patch')
            @include('admin.users._form', ['user' => $user, 'roles' => $roles])
            <div class="bf-form-actions">
                <button type="submit" class="bf-btn-primary">Actualizar</button>
                <a href="{{ route('admin.users.show', $user) }}" class="bf-btn-ghost">Ver ficha</a>
                <a href="{{ $user->adminUsersListRoute() }}" class="bf-btn-ghost">Lista</a>
            </div>
        </form>
    </div>
@endsection
