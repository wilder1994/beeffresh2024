@extends('layouts.app')

@section('titulo', 'Editar usuario')
@section('cabecera', 'Editar usuario')

@section('contenido')
    <div class="py-6 max-w-4xl mx-auto px-4">
        <form method="post" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="bg-base-100 rounded-xl shadow p-6 space-y-6">
            @csrf
            @method('patch')
            @include('admin.users._form', ['user' => $user, 'roles' => $roles])
            <div class="flex gap-3">
                <button type="submit" class="btn bg-[var(--bf-red)] text-white border-0">Actualizar</button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost">Ver ficha</a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Lista</a>
            </div>
        </form>
    </div>
@endsection
