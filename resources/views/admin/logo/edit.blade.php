@extends('layouts.app')

@section('titulo', 'Editar Logos')
@section('cabecera', 'Editar Logos')

@section('contenido')
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Formulario Logo Principal --}}
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">Logo Principal</h2>

            @if (session('success_principal'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success_principal') }}
                </div>
            @endif

            <form action="{{ route('admin.logo.update', 'principal') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo Actual:</label>
                    @if ($logoPrincipal && $logoPrincipal->imagen)
                        <img src="{{ asset('storage/logos/' . $logoPrincipal->imagen) }}" alt="Logo principal" class="h-20 mb-2">
                    @else
                        <p class="text-sm text-gray-500">No hay logo cargado.</p>
                    @endif
                </div>

                <div class="mb-4">
                    <label for="imagen_principal" class="block text-sm font-medium text-gray-700">Subir nuevo logo:</label>
                    <input type="file" name="imagen" id="imagen_principal" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3">
                    @error('imagen')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="bg-[#7c2d12] text-white px-4 py-2 rounded hover:bg-[#5a1f0c]">
                    Guardar
                </button>
            </form>
        </div>

        {{-- Formulario Logo Administrador --}}
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">Logo Administrador</h2>

            @if (session('success_administrador'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success_administrador') }}
                </div>
            @endif

            <form action="{{ route('admin.logo.update', 'administrador') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo Actual:</label>
                    @if ($logoAdministrador && $logoAdministrador->imagen)
                        <img src="{{ asset('storage/logos/' . $logoAdministrador->imagen) }}" alt="Logo administrador" class="h-20 mb-2">
                    @else
                        <p class="text-sm text-gray-500">No hay logo cargado.</p>
                    @endif
                </div>

                <div class="mb-4">
                    <label for="imagen_admin" class="block text-sm font-medium text-gray-700">Subir nuevo logo:</label>
                    <input type="file" name="imagen" id="imagen_admin" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3">
                    @error('imagen')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="bg-[#7c2d12] text-white px-4 py-2 rounded hover:bg-[#5a1f0c]">
                    Guardar
                </button>
            </form>
        </div>
    </div>
@endsection
