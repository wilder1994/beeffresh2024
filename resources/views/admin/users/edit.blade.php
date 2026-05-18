@extends('layouts.app')

@section('titulo', 'Editar usuario')
@section('cabecera', 'Editar usuario')

@section('contenido')
    <div class="py-4 px-3 sm:px-4">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('success') }}</div>
        @endif
        <x-account.shell
            :user="$user"
            mode="edit"
            context="admin"
            :back-url="route('admin.users.show', $user)"
        >
            <livewire:admin.user-form :user-id="$user->id" />
        </x-account.shell>
    </div>
@endsection
