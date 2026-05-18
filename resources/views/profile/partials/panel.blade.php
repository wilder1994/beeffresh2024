@php
    $user = $user ?? auth()->user();
    $user->loadMissing(['customerProfile', 'supplierProfile', 'roles']);
    $tabs = [
        ['id' => 'cuenta', 'label' => 'Cuenta'],
        ['id' => 'seguridad', 'label' => 'Seguridad'],
        ['id' => 'peligro', 'label' => 'Eliminar'],
    ];
    $inModal = $inModal ?? false;
@endphp

@if(session('error'))
    <section class="mb-3 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900">{{ session('error') }}</section>
@endif

@if(session('status') === 'profile-updated' || session('status') === 'password-updated')
    <section class="mb-3 rounded-lg border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-900">
        {{ session('status') === 'password-updated' ? 'Contraseña actualizada.' : 'Perfil guardado.' }}
    </section>
@endif

<x-account.shell
    :user="$user"
    mode="edit"
    context="self"
    :tabs="$tabs"
    :in-modal="$inModal"
    :editable-avatar="true"
    :back-url="$inModal ? null : (auth()->user()->isStaff() ? route('dashboard') : route('home'))"
>
    @if($inModal)
        <x-slot:headerActions>
            <button type="button" class="bf-btn-ghost btn-sm" x-on:click="$dispatch('close-modal', 'profile-account')">Cerrar</button>
        </x-slot:headerActions>
    @endif

    <section x-show="tab === 'cuenta'" x-cloak>
        @include('profile.partials.update-profile-information-form', ['inModal' => $inModal, 'user' => $user])
    </section>
    <section x-show="tab === 'seguridad'" x-cloak>
        @include('profile.partials.update-password-form', ['inModal' => $inModal])
    </section>
    <section x-show="tab === 'peligro'" x-cloak>
        @include('profile.partials.delete-user-form')
    </section>
</x-account.shell>
