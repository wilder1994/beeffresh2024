@extends('layouts.app')

@section('titulo', 'Nuevo Video')
@section('cabecera', 'Agregar video · página principal')

@section('contenido')
    <div class="max-w-2xl mx-auto px-3 sm:px-4 py-4">
        <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf

            <div class="space-y-3">
                <div>
                    <label class="bf-label" for="vid-titulo">Título del video</label>
                    <input id="vid-titulo" type="text" name="titulo" class="bf-input" required>
                </div>

                <div>
                    <label class="bf-label" for="tipo">Tipo de video</label>
                    <select name="tipo" id="tipo" class="bf-select" required onchange="toggleInputs()">
                        <option value="youtube">Enlace de YouTube</option>
                        <option value="archivo">Subir desde el equipo</option>
                    </select>
                </div>

                <div id="urlInput">
                    <label class="bf-label" for="vid-url">Enlace de YouTube</label>
                    <input id="vid-url" type="url" name="url" class="bf-input" placeholder="https://youtube.com/…" />
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
