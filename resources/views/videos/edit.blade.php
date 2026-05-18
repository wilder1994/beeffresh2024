@extends('layouts.app')

@section('titulo', 'Editar Video')
@section('cabecera', 'Editar video · página principal')

@section('contenido')
    <div class="max-w-2xl mx-auto px-3 sm:px-4 py-4">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-900" role="alert">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('videos.update', $video->id) }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <div>
                    <label class="bf-label" for="evid-titulo">Título del video</label>
                    <input id="evid-titulo" type="text" name="titulo" class="bf-input" value="{{ old('titulo', $video->titulo) }}" required>
                </div>

                <div>
                    <label class="bf-label" for="tipo">Tipo de video</label>
                    <select name="tipo" id="tipo" class="bf-select" required onchange="toggleInputs()">
                        <option value="youtube" {{ old('tipo', $video->tipo) === 'youtube' ? 'selected' : '' }}>Enlace de YouTube</option>
                        <option value="archivo" {{ old('tipo', $video->tipo) === 'archivo' ? 'selected' : '' }}>Subido desde el equipo</option>
                    </select>
                </div>

                <div id="urlInput">
                    <label class="bf-label" for="evid-url">Enlace de YouTube</label>
                    <input id="evid-url" type="url" name="url" class="bf-input" value="{{ old('url', $video->tipo === 'youtube' ? $video->url : '') }}" placeholder="https://www.youtube.com/watch?v=… o https://youtu.be/…" />
                    @error('url')
                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div id="archivoInput" class="hidden">
                    <label class="bf-label" for="evid-archivo">Archivo de video (mp4, webm, ogg)</label>
                    <input id="evid-archivo" type="file" name="archivo" accept="video/mp4,video/webm,video/ogg" class="bf-file" />
                    @if ($video->archivo)
                        <p class="text-xs mt-2 text-stone-600">Archivo actual: {{ $video->archivo }}</p>
                        <video controls class="mt-2 w-full max-h-48 rounded-lg border border-stone-200">
                            <source src="{{ asset('storage/videos/' . $video->archivo) }}" type="video/mp4">
                        </video>
                    @endif
                </div>
            </div>

            <div class="bf-form-actions justify-between gap-2">
                <a href="{{ route('videos.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Actualizar</button>
            </div>
        </form>
    </div>

    <script>
        function toggleInputs() {
            const tipo = document.getElementById('tipo').value;
            const urlInput = document.getElementById('urlInput');
            const archivoInput = document.getElementById('archivoInput');

            if (tipo === 'youtube') {
                urlInput.classList.remove('hidden');
                archivoInput.classList.add('hidden');
            } else {
                urlInput.classList.add('hidden');
                archivoInput.classList.remove('hidden');
            }

            document.querySelector('[name="url"]').required = (tipo === 'youtube');
            document.querySelector('[name="archivo"]').required = (tipo === 'archivo');
        }

        document.addEventListener('DOMContentLoaded', toggleInputs);
    </script>
@endsection
