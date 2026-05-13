@extends('layouts.app')

@section('titulo', 'Nuevo usuario')
@section('cabecera', 'Crear usuario')

@section('contenido')
    <div class="py-4 md:py-6 max-w-4xl mx-auto px-3 sm:px-4">
        <livewire:admin.user-form />
    </div>
@endsection
