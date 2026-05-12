@extends('layouts.app')

@section('titulo', 'Editar receta')
@section('cabecera', 'Editar receta')

@section('contenido')
    <div class="max-w-2xl mx-auto px-3 sm:px-4 py-4">
        <form action="{{ route('recetas.update', $receta->id) }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <div>
                    <label class="bf-label" for="erec-titulo">Título</label>
                    <input id="erec-titulo" type="text" name="titulo" class="bf-input" value="{{ old('titulo', $receta->titulo) }}" required>
                </div>

                <div>
                    <label class="bf-label" for="erec-tipo">Tipo</label>
                    <select id="erec-tipo" name="tipo" class="bf-select" required onchange="mostrarCampos(this.value)">
                        <option value="youtube" {{ $receta->tipo === 'youtube' ? 'selected' : '' }}>YouTube</option>
                        <option value="archivo" {{ $receta->tipo === 'archivo' ? 'selected' : '' }}>Archivo</option>
                    </select>
                </div>

                <div id="campo_youtube">
                    <label class="bf-label" for="erec-url">URL de YouTube</label>
                    <input id="erec-url" type="url" name="url" class="bf-input" value="{{ old('url', $receta->url) }}">
                </div>

                <div id="campo_archivo">
                    <label class="bf-label" for="erec-archivo">Archivo de video</label>
                    @if ($receta->archivo)
                        <p class="text-xs text-stone-600 mb-1">Video actual: {{ $receta->archivo }}</p>
                    @endif
                    <input id="erec-archivo" type="file" name="archivo" accept="video/*" class="bf-file">
                </div>
            </div>

            <div class="bf-form-actions justify-end gap-2">
                <a href="{{ route('recetas.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Actualizar receta</button>
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

        document.addEventListener('DOMContentLoaded', function() {
            mostrarCampos(document.querySelector('select[name="tipo"]').value);
        });
    </script>
@endsection
