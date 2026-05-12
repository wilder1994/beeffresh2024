@extends('layouts.app')

@section('titulo', 'Nueva receta')
@section('cabecera', 'Crear nueva receta')

@section('contenido')
    <div class="max-w-2xl mx-auto px-3 sm:px-4 py-4">
        <form action="{{ route('recetas.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf

            <div class="space-y-3">
                <div>
                    <label class="bf-label" for="rec-titulo">Título</label>
                    <input id="rec-titulo" type="text" name="titulo" class="bf-input" required>
                </div>

                <div>
                    <label class="bf-label" for="rec-tipo">Tipo</label>
                    <select id="rec-tipo" name="tipo" class="bf-select" required onchange="mostrarCampos(this.value)">
                        <option value="youtube">YouTube</option>
                        <option value="archivo">Archivo</option>
                    </select>
                </div>

                <div id="campo_youtube">
                    <label class="bf-label" for="rec-url">URL de YouTube</label>
                    <input id="rec-url" type="url" name="url" class="bf-input">
                </div>

                <div id="campo_archivo" class="hidden">
                    <label class="bf-label" for="rec-archivo">Archivo de video</label>
                    <input id="rec-archivo" type="file" name="archivo" accept="video/*" class="bf-file">
                </div>
            </div>

            <div class="bf-form-actions justify-end gap-2">
                <a href="{{ route('recetas.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Guardar receta</button>
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
