@extends('layouts.app')

@section('titulo', 'Nuevo Video')
@section('cabecera', 'Agregar video · página principal')

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

        <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf

            <div class="space-y-3">
                <div>
                    <label class="bf-label" for="vid-titulo">Título del video</label>
                    <input id="vid-titulo" type="text" name="titulo" class="bf-input" value="{{ old('titulo') }}" required>
                </div>

                <div>
                    <label class="bf-label" for="tipo">Tipo de video</label>
                    <select name="tipo" id="tipo" class="bf-select" required onchange="toggleInputs()">
                        <option value="youtube" {{ old('tipo', 'youtube') === 'youtube' ? 'selected' : '' }}>Enlace de YouTube</option>
                        <option value="archivo" {{ old('tipo') === 'archivo' ? 'selected' : '' }}>Subir desde el equipo</option>
                    </select>
                </div>

                <div id="urlInput">
                    <label class="bf-label" for="vid-url">Enlace de YouTube</label>
                    <input id="vid-url" type="url" name="url" class="bf-input" value="{{ old('url') }}" placeholder="https://www.youtube.com/watch?v=… o https://youtu.be/…" />
                    @error('url')
                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div id="archivoInput" class="hidden">
                    <label class="bf-label" for="vid-archivo">Archivo de video (mp4)</label>
                    <input id="vid-archivo" type="file" name="archivo" accept="video/mp4,video/webm,video/ogg" class="bf-file" />
                </div>
            </div>

            <div class="bf-form-actions justify-between gap-2">
                <a href="{{ route('videos.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Guardar</button>
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
                document.querySelector('[name="url"]').required = true;
                document.querySelector('[name="archivo"]').required = false;
            } else {
                urlInput.classList.add('hidden');
                archivoInput.classList.remove('hidden');
                document.querySelector('[name="url"]').required = false;
                document.querySelector('[name="archivo"]').required = true;
            }
        }

        document.addEventListener('DOMContentLoaded', toggleInputs);
    </script>
@endsection
