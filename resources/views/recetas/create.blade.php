@extends('layouts.app')

@section('titulo', 'Nueva receta')
@section('cabecera', 'Crear nueva receta')

@section('contenido')
<div class="max-w-2xl mx-auto mt-6 bg-white p-6 rounded shadow">
    <form action="{{ route('recetas.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="label">Título</label>
            <input type="text" name="titulo" class="input input-bordered w-full" required>
        </div>

        <div class="mb-4">
            <label class="label">Tipo</label>
            <select name="tipo" class="select select-bordered w-full" required onchange="mostrarCampos(this.value)">
                <option value="youtube">YouTube</option>
                <option value="archivo">Archivo</option>
            </select>
        </div>

        <div id="campo_youtube" class="mb-4">
            <label class="label">URL de YouTube</label>
            <input type="url" name="url" class="input input-bordered w-full">
        </div>

        <div id="campo_archivo" class="mb-4 hidden">
            <label class="label">Archivo de video</label>
            <input type="file" name="archivo" accept="video/*" class="file-input w-full">
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('recetas.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar receta</button>
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

    // Carga inicial
    document.addEventListener('DOMContentLoaded', function() {
        const tipo = document.querySelector('select[name="tipo"]').value;
        mostrarCampos(tipo);
    });
</script>
@endsection
