@extends('layouts.app')

@section('titulo', 'Editar corte')
@section('cabecera', 'Editar corte: ' . $corte->nombre)

@section('contenido')
    <div class="flex justify-center my-6">
        <div class="card w-full max-w-xl bg-base-100 shadow-xl">
            <form action="{{ route('cortes.update', $corte) }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')

                {{-- Nombre --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Nombre del corte</span>
                    </label>
                    <input type="text" name="nombre" class="input input-bordered" value="{{ old('nombre', $corte->nombre) }}" required />
                </div>

                {{-- Descripción --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Descripción</span>
                    </label>
                    <textarea name="descripcion" class="textarea textarea-bordered" rows="4">{{ old('descripcion', $corte->descripcion) }}</textarea>
                </div>

                {{-- Imagen actual --}}
                @if ($corte->imagen)
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Imagen actual</span>
                        </label>
                        <img src="{{ asset('storage/cortes/' . $corte->imagen) }}" class="rounded-lg w-48 h-auto mx-auto" alt="{{ $corte->nombre }}">
                    </div>
                @endif

                {{-- Nueva imagen --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Actualizar imagen (opcional)</span>
                    </label>
                    <input type="file" name="imagen" class="file-input file-input-bordered" accept="image/*" />
                </div>

                {{-- Botones --}}
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">Actualizar corte</button>
                    <a href="{{ route('cortes.index') }}" class="btn btn-outline mt-2">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
