@extends('layouts.app')

@section('titulo', 'Editar receta')
@section('cabecera', 'Editar receta')

@section('contenido')
<div class="max-w-2xl mx-auto mt-6 bg-white p-6 rounded shadow">
    <form action="{{ route('recetas.update', $receta->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="label">Título</label>
            <input type="text" name="titulo" class="input input-bordered w-full" value="{{ old('titulo', $receta->titulo) }}" required>
        </div>

        <div class="mb-4">
            <label class="label">Tipo</label>
            <select name="tipo" class="select select-bordered w-full" required onchange="mostrarCampos(this.value)">
                <option value="youtube" {{ $receta->tipo === 'youtube' ? 'selected' : '' }}>YouTube</option>
                <option value="archivo" {{ $receta->tipo === 'archivo' ? 'selected' : '' }}>Archivo</option>
            </select>
        </div>

        <div id="campo_youtube" class="mb-4">
            <label class="label">URL de YouTube</label>
            <input type="url" name="url" class="input input-bordered w-full" value="{{ old('url', $receta->url) }}">
        </div>

        <div id="campo_archivo" class="mb-4">
            <label class="label">Archivo de video</label>
            @if ($receta->archivo)
                <p class="mb-2 text-sm text-gray-600">Video actual: {{ $receta->archivo }}</p>
            @endif
            <input type="file" name="archivo" accept="video/*" class="file-input w-full">
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('recetas.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar receta</button>
        </div>
    </form>
</div>

<script>
    function mostrarCampos(tipo) {
        document.getElementById('campo_youtube').classList.add('hidden');
        document.getElementById('campo_archivo').classList.add('hidden');

        if (tipo === 'youtube') {
            document.getElementById('campo_youtube').classList.remove('hidden');
        } else if (tipo === 'archivo') {
            document.getElementById('campo_archivo').classList.remove('hidden');
        }
    }

    // Carga inicial con el tipo ya seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        const tipo = document.querySelector('select[name="tipo"]').value;
        mostrarCampos(tipo);
    });
</script>
@endsection
