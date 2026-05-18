@props([
    'user' => null,
    'mode' => 'view',
    'context' => 'admin',
    'backUrl' => null,
    'editUrl' => null,
    'tabs' => [],
    'inModal' => false,
    'editableAvatar' => false,
    'avatarFormId' => 'profile-update-form',
])

@php
    $shellClass = 'bf-account-shell '.($inModal ? 'bf-account-shell--modal' : 'max-w-4xl mx-auto');
    $avatarEdit = $editableAvatar && $user;
    $avatarInitial = $user ? mb_strtoupper(mb_substr($user->name, 0, 1, 'UTF-8')) : '';
@endphp

<div
    @if($avatarEdit)
        x-data="avatarEditor({
            preview: @js($user->avatarUrl()),
            initial: @js($avatarInitial),
            inputId: 'profile-avatar-input',
        })"
    @endif
    {{ $attributes->merge(['class' => $shellClass]) }}
>
    <x-account.header
        :user="$user"
        :mode="$mode"
        :context="$context"
        :editable-avatar="$avatarEdit"
        :avatar-form-id="$avatarFormId"
    >
        <x-slot:actions>
            @if($editUrl && $mode === 'view')
                <a href="{{ $editUrl }}" class="bf-btn-primary btn-sm">Editar</a>
            @endif
            @if($backUrl && ! $inModal)
                <a href="{{ $backUrl }}" class="bf-btn-ghost btn-sm">Volver</a>
            @endif
            @if(isset($headerActions))
                {{ $headerActions }}
            @endif
        </x-slot:actions>
    </x-account.header>

    @if($avatarEdit)
        @error('avatar')
            <p class="text-xs text-red-600 -mt-2 mb-3">{{ $message }}</p>
        @enderror
    @endif

    @if(count($tabs) > 0)
        <section class="mt-4" x-data="{ tab: @js($tabs[0]['id'] ?? 'cuenta') }">
            <x-account.tabs :tabs="$tabs" />
            <section class="bf-account-shell__body mt-4">
                {{ $slot }}
            </section>
        </section>
    @else
        <section class="bf-account-shell__body mt-4">
            {{ $slot }}
        </section>
    @endif

    @if($avatarEdit)
        <x-avatar.crop-dialog />
    @endif

    @if($user && $mode === 'view')
        <p class="mt-4 pt-3 border-t border-stone-100 text-[11px] text-stone-500">
            Registrado {{ $user->created_at->format('d/m/Y H:i') }}
            @if($user->updated_at && $user->updated_at->ne($user->created_at))
                · Actualizado {{ $user->updated_at->format('d/m/Y H:i') }}
            @endif
        </p>
    @endif
</div>
