@extends('layouts.app')

@section('titulo', 'Editar corte')
@section('cabecera', 'Editar corte: ' . $corte->nombre)

@section('contenido')
    <div class="max-w-xl mx-auto px-3 sm:px-4 py-4">
        <form action="{{ route('cortes.update', $corte) }}" method="POST" enctype="multipart/form-data" class="bf-form-panel bf-form-panel-tight space-y-3">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <div>
                    <label class="bf-label" for="ecorte-nombre">Nombre del corte</label>
                    <input id="ecorte-nombre" type="text" name="nombre" class="bf-input" value="{{ old('nombre', $corte->nombre) }}" required />
                </div>

                <div>
                    <label class="bf-label" for="ecorte-desc">Descripción</label>
                    <textarea id="ecorte-desc" name="descripcion" class="bf-textarea min-h-[5rem]" rows="3">{{ old('descripcion', $corte->descripcion) }}</textarea>
                </div>

                @if ($corte->imagen)
                    <div>
                        <span class="bf-label normal-case">Imagen actual</span>
                        <img src="{{ asset('storage/cortes/' . $corte->imagen) }}" class="rounded-lg w-44 max-h-36 object-cover border border-stone-200 mt-1" alt="{{ $corte->nombre }}">
                    </div>
                @endif

                <div>
                    <label class="bf-label" for="ecorte-img">Actualizar imagen (opcional)</label>
                    <input id="ecorte-img" type="file" name="imagen" class="bf-file" accept="image/*" />
                </div>
            </div>

            <div class="bf-form-actions flex-col sm:flex-row">
                <button type="submit" class="bf-btn-primary w-full sm:w-auto">Actualizar corte</button>
                <a href="{{ route('cortes.index') }}" class="bf-btn-ghost w-full sm:w-auto justify-center">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
