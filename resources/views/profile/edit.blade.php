@extends('layouts.app')

@section('titulo', __('Profile'))
@section('cabecera', __('Mi perfil'))

@section('contenido')
    <div class="py-6 max-w-4xl mx-auto px-4 space-y-10">
        <div class="bg-base-100 p-6 rounded-2xl shadow-md">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="bg-base-100 p-6 rounded-2xl shadow-md">
            @include('profile.partials.update-password-form')
        </div>

        <div class="bg-base-100 p-6 rounded-2xl shadow-md">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
@endsection
