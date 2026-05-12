@extends('layouts.app')

@section('titulo', 'Crear Promoción')
@section('cabecera', 'Nueva Promoción')

@section('contenido')
    <div class="max-w-xl mx-auto px-3 sm:px-4 py-4">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('promociones.store') }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf

            <div class="space-y-3">
                <div>
                    <label class="bf-label" for="promo-titulo">Título</label>
                    <input id="promo-titulo" type="text" name="titulo" class="bf-input" required>
                </div>

                <div>
                    <label class="bf-label" for="promo-desc">Descripción</label>
                    <textarea id="promo-desc" name="descripcion" class="bf-textarea min-h-[5rem]" rows="3"></textarea>
                </div>

                <div>
                    <label class="bf-label" for="promo-img">Imagen</label>
                    <input id="promo-img" type="file" name="imagen" accept="image/*" class="bf-file">
                </div>

                <div>
                    <label class="bf-label" for="promo-enlace">Enlace (opcional)</label>
                    <input id="promo-enlace" type="url" name="enlace" class="bf-input" placeholder="https://…">
                </div>
            </div>

            <div class="bf-form-actions justify-between gap-2">
                <a href="{{ route('promociones.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection
