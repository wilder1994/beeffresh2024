@extends('layouts.app')

@section('titulo', 'Editar Promoción')
@section('cabecera', 'Editar Promoción')

@section('contenido')
    <div class="max-w-xl mx-auto p-6">
        <form action="{{ route('promociones.update', $promocion->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-control mb-4">
                <label class="label">Título</label>
                <input type="text" name="titulo" class="input input-bordered" value="{{ $promocion->titulo }}" required>
            </div>

            <div class="form-control mb-4">
                <label class="label">Descripción</label>
                <textarea name="descripcion" class="textarea textarea-bordered">{{ $promocion->descripcion }}</textarea>
            </div>

            <div class="form-control mb-4">
                <label class="label">Imagen actual:</label>
                @if($promocion->imagen)
                    <img src="{{ asset('storage/' . $promocion->imagen) }}" alt="Promo" class="mb-2 rounded">
                @endif
                <input type="file" name="imagen" class="file-input file-input-bordered" accept="image/*">
            </div>

            <div class="form-control mb-4">
                <label class="label">Enlace (opcional)</label>
                <input type="url" name="enlace" class="input input-bordered" value="{{ $promocion->enlace }}">
            </div>

            <button class="btn btn-primary">Actualizar</button>
            <a href="{{ route('promociones.index') }}" class="btn btn-outline ml-4">Cancelar</a>
        </form>
    </div>
@endsection
