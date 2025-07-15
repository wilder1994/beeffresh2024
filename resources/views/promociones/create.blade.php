@extends('layouts.app')

@section('titulo', 'Crear Promoción')
@section('cabecera', 'Nueva Promoción')

@section('contenido')
    <div class="max-w-xl mx-auto p-6">
        <form action="{{ route('promociones.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-control mb-4">
                <label class="label">Título</label>
                <input type="text" name="titulo" class="input input-bordered" required>
            </div>

            <div class="form-control mb-4">
                <label class="label">Descripción</label>
                <textarea name="descripcion" class="textarea textarea-bordered"></textarea>
            </div>

            <div class="form-control mb-4">
                <label class="label">Imagen</label>
                <input type="file" name="imagen" class="file-input file-input-bordered" accept="image/*">
            </div>

            <div class="form-control mb-4">
                <label class="label">Enlace (opcional)</label>
                <input type="url" name="enlace" class="input input-bordered">
            </div>

            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('promociones.index') }}" class="btn btn-outline ml-4">Cancelar</a>
        </form>
    </div>
@endsection
