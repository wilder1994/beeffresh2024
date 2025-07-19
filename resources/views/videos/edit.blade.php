@extends('layouts.app')

@section('titulo', 'Editar Video')
@section('cabecera', 'Editar Video de la Página Principal')

@section('contenido')
<div class="max-w-2xl mx-auto p-6 bg-base-100 rounded-xl shadow">
    <h2 class="text-xl font-semibold mb-4">Editar Video</h2>

    <form action="{{ route('videos.update', $video->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Título --}}
        <div class="form-control mb-4">
            <label class="label font-bold">Título del video</label>
            <input type="text" name="titulo" class="input input-bordered" value="{{ $video->titulo }}" required>
        </div>

        {{-- Tipo de video --}}
        <div class="form-control mb-4">
            <label class="label font-bold">Tipo de video</label>
            <select name="tipo" id="tipo" class="select select-bordered" required onchange="toggleInputs()">
                <option value="youtube" {{ $video->tipo === 'youtube' ? 'selected' : '' }}>Enlace de YouTube</option>
                <option value="archivo" {{ $video->tipo === 'archivo' ? 'selected' : '' }}>Subido desde el equipo</option>
            </select>
        </div>

        {{-- Enlace de YouTube --}}
        <div class="form-control mb-4" id="urlInput">
            <label class="label font-bold">Enlace de YouTube</label>
            <input type="url" name="url" class="input input-bordered" value="{{ $video->url }}" placeholder="https://youtube.com/..." />
        </div>

        {{-- Subida de archivo --}}
        <div class="form-control mb-4 hidden" id="archivoInput">
            <label class="label font-bold">Archivo de video (mp4, webm, ogg)</label>
            <input type="file" name="archivo" accept="video/mp4,video/webm,video/ogg" class="file-input file-input-bordered" />
            @if ($video->archivo)
                <p class="text-sm mt-2 text-gray-500">Archivo actual: {{ $video->archivo }}</p>
                <video controls class="mt-2 w-full">
                    <source src="{{ asset('storage/videos/' . $video->archivo) }}" type="video/mp4">
                    Tu navegador no soporta este video.
                </video>
            @endif
        </div>

        {{-- Botones --}}
        <div class="form-control mt-6 flex gap-4">
            <button type="submit" class="btn btn-primary">Actualizar</button>
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

        // Mostrar u ocultar campos
        if (tipo === 'youtube') {
            urlInput.classList.remove('hidden');
            archivoInput.classList.add('hidden');
        } else {
            urlInput.classList.add('hidden');
            archivoInput.classList.remove('hidden');
        }

        // Requeridos dinámicos
        document.querySelector('[name="url"]').required = (tipo === 'youtube');
        document.querySelector('[name="archivo"]').required = (tipo === 'archivo');
    }

    document.addEventListener('DOMContentLoaded', toggleInputs);
</script>
@endsection
