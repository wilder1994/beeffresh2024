@extends('layouts.app')

@section('titulo', 'Crear corte')
@section('cabecera', 'Nuevo corte')

@section('contenido')
    <div class="flex justify-center my-6">
        <div class="card w-full max-w-xl bg-base-100 shadow-xl">
            <form action="{{ route('cortes.store') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf

                {{-- Nombre --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Nombre del corte</span>
                    </label>
                    <input type="text" name="nombre" class="input input-bordered" required placeholder="Ej: Lomo fino" />
                </div>

                {{-- Descripción --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Descripción</span>
                    </label>
                    <textarea name="descripcion" class="textarea textarea-bordered" placeholder="Descripción del corte" rows="4"></textarea>
                </div>

                {{-- Imagen --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Imagen del corte</span>
                    </label>
                    <input type="file" name="imagen" class="file-input file-input-bordered" accept="image/*" required />
                </div>

                {{-- Botones --}}
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">Guardar corte</button>
                    <a href="{{ route('cortes.index') }}" class="btn btn-outline mt-2">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
