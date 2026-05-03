@extends('layouts.app')

@section('titulo', 'Nuevo usuario')
@section('cabecera', 'Crear usuario')

@section('contenido')
    <div class="py-6 max-w-4xl mx-auto px-4">
        <form method="post" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="bg-base-100 rounded-xl shadow p-6 space-y-6">
            @csrf
            @include('admin.users._form', ['user' => null, 'roles' => $roles])
            <div class="flex gap-3">
                <button type="submit" class="btn bg-[var(--bf-red)] text-white border-0">Guardar</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
