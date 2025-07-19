@extends('layouts.app')

@section('titulo', 'Nuevo Video')
@section('cabecera', 'Agregar Video a la Página Principal')

@section('contenido')
<div class="max-w-2xl mx-auto p-6 bg-base-100 rounded-xl shadow">
    <h2 class="text-xl font-semibold mb-4">Nuevo Video</h2>

    <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Título --}}
        <div class="form-control mb-4">
            <label class="label font-bold">Título del video</label>
            <input type="text" name="titulo" class="input input-bordered" required>
        </div>

        {{-- Tipo de video --}}
        <div class="form-control mb-4">
            <label class="label font-bold">Tipo de video</label>
            <select name="tipo" id="tipo" class="select select-bordered" required onchange="toggleInputs()">
                <option value="youtube">Enlace de YouTube</option>
                <option value="archivo">Subir desde el equipo</option>
            </select>
        </div>

        {{-- Enlace de YouTube --}}
        <div class="form-control mb-4" id="urlInput">
            <label class="label font-bold">Enlace de YouTube</label>
            <input type="url" name="url" class="input input-bordered" placeholder="https://youtube.com/..." />
        </div>

        {{-- Subida de archivo --}}
        <div class="form-control mb-4 hidden" id="archivoInput">
            <label class="label font-bold">Archivo de video (mp4)</label>
            <input type="file" name="archivo" accept="video/mp4,video/webm,video/ogg" class="file-input file-input-bordered" />
        </div>

        {{-- Botones --}}
        <div class="form-control mt-6 flex gap-4">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('videos.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

{{-- Script para mostrar/ocultar inputs --}}
<script>
    function toggleInputs() {
        const tipo = document.getElementById('tipo').value;
        const urlInput = document.getElementById('urlInput');
        const archivoInput = document.getElementById('archivoInput');

        if (tipo === 'youtube') {
            urlInput.classList.remove('hidden');
            archivoInput.classList.add('hidden');
            document.querySelector('[name="url"]').required = true;
            document.querySelector('[name="archivo"]').required = false;
        } else {
            urlInput.classList.add('hidden');
            archivoInput.classList.remove('hidden');
            document.querySelector('[name="url"]').required = false;
            document.querySelector('[name="archivo"]').required = true;
        }

    }

    // Ejecutar al cargar por si acaso
    document.addEventListener('DOMContentLoaded', toggleInputs);
</script>
@endsection
