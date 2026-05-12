@extends('layouts.app')

@section('titulo', 'Crear corte')
@section('cabecera', 'Nuevo corte')

@section('contenido')
    <div class="max-w-xl mx-auto px-3 sm:px-4 py-4">
        <form action="{{ route('cortes.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf

            <div class="space-y-3">
                <div>
                    <label class="bf-label" for="corte-nombre">Nombre del corte</label>
                    <input id="corte-nombre" type="text" name="nombre" class="bf-input" required placeholder="Ej: Lomo fino" />
                </div>

                <div>
                    <label class="bf-label" for="corte-desc">Descripción</label>
                    <textarea id="corte-desc" name="descripcion" class="bf-textarea min-h-[5rem]" placeholder="Descripción del corte" rows="3"></textarea>
                </div>

                <div>
                    <label class="bf-label" for="corte-img">Imagen del corte</label>
                    <input id="corte-img" type="file" name="imagen" class="bf-file" accept="image/*" required />
                </div>
            </div>

            <div class="bf-form-actions flex-col sm:flex-row">
                <button type="submit" class="bf-btn-primary w-full sm:w-auto">Guardar corte</button>
                <a href="{{ route('cortes.index') }}" class="bf-btn-ghost w-full sm:w-auto justify-center">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
