@extends('layouts.app')

@section('titulo', 'Editar Promoción')
@section('cabecera', 'Modificar Promoción')

@section('contenido')
<div class="max-w-xl mx-auto p-6 bg-base-100 shadow-xl rounded-xl">
    <form action="{{ route('promociones.update', $promocion) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-control mb-4">
            <label class="label font-bold">Título</label>
            <input type="text" name="titulo" class="input input-bordered" value="{{ old('titulo', $promocion->titulo) }}" required>
        </div>

        <div class="form-control mb-4">
            <label class="label font-bold">Descripción</label>
            <textarea name="descripcion" class="textarea textarea-bordered" rows="4">{{ old('descripcion', $promocion->descripcion) }}</textarea>
        </div>

        <div class="form-control mb-4">
            <label class="label font-bold">Imagen actual</label>
            @if($promocion->imagen)
                <img src="{{ asset('storage/promociones/' . $promocion->imagen) }}" class="w-full h-40 object-cover rounded-xl mb-2">
            @else
                <p class="text-sm text-gray-500">No hay imagen cargada.</p>
            @endif
            <input type="file" name="imagen" accept="image/*" class="file-input file-input-bordered mt-2">
        </div>

        <div class="form-control mb-4">
            <label class="label font-bold">Enlace (opcional)</label>
            <input type="url" name="enlace" class="input input-bordered" value="{{ old('enlace', $promocion->enlace) }}">
        </div>

        <div class="form-control mt-6">
            <button class="btn btn-primary">Actualizar</button>
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
