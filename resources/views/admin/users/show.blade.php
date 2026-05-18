@extends('layouts.app')

@php
    use App\Domain\Users\RoleSlug;
    $roleSlug = $user->primaryRoleSlug();
    $tabs = [['id' => 'cuenta', 'label' => 'Cuenta']];
    if ($user->isEmployee()) {
        $tabs[] = ['id' => 'empleado', 'label' => 'Empleado'];
    }
    if ($user->isCustomer()) {
        $tabs[] = ['id' => 'cliente', 'label' => 'Cliente'];
    }
    if ($user->isSupplier()) {
        $tabs[] = ['id' => 'proveedor', 'label' => 'Proveedor'];
    }
@endphp

@section('titulo', 'Usuario · '.$user->name)
@section('cabecera', 'Usuario')

@section('contenido')
    <div class="py-4 px-3 sm:px-4">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('success') }}</div>
        @endif

        <x-account.shell
            :user="$user"
            mode="view"
            context="admin"
            :tabs="$tabs"
            :back-url="$user->adminUsersListRoute()"
            :edit-url="route('admin.users.edit', $user)"
        >
            @include('admin.users.partials.account-view', ['user' => $user])
        </x-account.shell>
    </div>
@endsection
