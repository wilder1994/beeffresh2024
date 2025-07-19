@extends('layouts.app')

@section('titulo', 'Crear Promoción')
@section('cabecera', 'Nueva Promoción')

@section('contenido')
<div class="max-w-xl mx-auto p-6 bg-base-100 shadow-xl rounded-xl">
    <form action="{{ route('promociones.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-control mb-4">
            <label class="label font-bold">Título</label>
            <input type="text" name="titulo" class="input input-bordered" required>
        </div>

        <div class="form-control mb-4">
            <label class="label font-bold">Descripción</label>
            <textarea name="descripcion" class="textarea textarea-bordered" rows="4"></textarea>
        </div>

        <div class="form-control mb-4">
            <label class="label font-bold">Imagen</label>
            <input type="file" name="imagen" accept="image/*" class="file-input file-input-bordered">
        </div>

        <div class="form-control mb-4">
            <label class="label font-bold">Enlace (opcional)</label>
            <input type="url" name="enlace" class="input input-bordered">
        </div>

        <div class="form-control mt-6">
            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('promociones.index') }}" class="btn btn-outline ml-4">Cancelar</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-error mt-4">
                <ul class="list-disc list-inside text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>
</div>
@endsection
