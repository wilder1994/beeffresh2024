@extends('layouts.app')

@section('titulo', 'Editar Promoción')
@section('cabecera', 'Modificar Promoción')

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

        <form action="{{ route('promociones.update', $promocion) }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <div>
                    <label class="bf-label" for="epromo-titulo">Título</label>
                    <input id="epromo-titulo" type="text" name="titulo" class="bf-input" value="{{ old('titulo', $promocion->titulo) }}" required>
                </div>

                <div>
                    <label class="bf-label" for="epromo-desc">Descripción</label>
                    <textarea id="epromo-desc" name="descripcion" class="bf-textarea min-h-[5rem]" rows="3">{{ old('descripcion', $promocion->descripcion) }}</textarea>
                </div>

                <div>
                    <span class="bf-label normal-case">Imagen actual</span>
                    @if($promocion->imagen)
                        <img src="{{ asset('storage/promociones/' . $promocion->imagen) }}" class="w-full max-h-40 object-cover rounded-lg border border-stone-200 mt-1" alt="">
                    @else
                        <p class="text-xs text-stone-500 mt-1">Sin imagen.</p>
                    @endif
                    <label class="bf-label mt-2" for="epromo-img">Nueva imagen (opcional)</label>
                    <input id="epromo-img" type="file" name="imagen" accept="image/*" class="bf-file">
                </div>

                <div>
                    <label class="bf-label" for="epromo-enlace">Enlace (opcional)</label>
                    <input id="epromo-enlace" type="url" name="enlace" class="bf-input" value="{{ old('enlace', $promocion->enlace) }}">
                </div>
            </div>

            <div class="bf-form-actions justify-between gap-2">
                <a href="{{ route('promociones.index') }}" class="bf-btn-ghost">Cancelar</a>
                <button type="submit" class="bf-btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@endsection
