@extends('layouts.app')

@section('titulo', 'Editar usuario')
@section('cabecera', 'Editar usuario')

@section('contenido')
    <div class="py-4 md:py-6 max-w-4xl mx-auto px-3 sm:px-4">
        <livewire:admin.user-form :user-id="$user->id" />
    </div>
@endsection
