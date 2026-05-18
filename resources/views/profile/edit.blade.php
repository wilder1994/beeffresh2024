@extends('layouts.app')

@section('titulo', 'Mi perfil')
@section('cabecera', 'Mi perfil')

@section('contenido')
    <div class="py-4 px-3 sm:px-4">
        @include('profile.partials.panel', ['user' => $user, 'inModal' => false])
    </div>
@endsection
