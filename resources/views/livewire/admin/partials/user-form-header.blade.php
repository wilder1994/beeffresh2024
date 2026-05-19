@php
    use App\Domain\Users\RoleSlug;
    $displayName = trim($first_name.' '.$last_name);
    $title = $displayName !== '' ? $displayName : ($userId ? 'Editar usuario' : 'Nuevo usuario');
    $ufAvatarInitial = mb_strtoupper(mb_substr(trim($first_name) !== '' ? $first_name : 'U', 0, 1, 'UTF-8'));
@endphp

<header
    wire:key="avatar-editor-header-{{ $userId ?? 'new' }}-{{ $existing_avatar_url ?? 'none' }}"
    class="flex flex-wrap items-start gap-4 border-b border-stone-200 pb-4 mb-4"
    x-data="avatarEditor({
        preview: @js($existing_avatar_url),
        initial: @js($ufAvatarInitial),
        inputId: 'uf-avatar',
        useLivewire: true,
    })"
>
    @include('profile.partials.avatar-field', [
        'inputId' => 'uf-avatar',
        'size' => 'h-16 w-16',
        'forLivewire' => true,
    ])

    <section class="min-w-0 flex-1">
        <h2 class="text-lg font-bold text-stone-900 truncate">{{ $title }}</h2>
        @if($email)
            <p class="text-sm text-stone-600 truncate">{{ $email }}</p>
        @endif
        @if($phone)
            <p class="text-sm text-stone-500">{{ $phone }}</p>
        @endif
        <section class="flex flex-wrap gap-1.5 mt-2">
            <span class="badge badge-sm border border-stone-200 bg-stone-100 text-stone-700">{{ RoleSlug::audienceLabel($role_slug) }}</span>
            <span class="badge badge-sm badge-ghost text-stone-600">{{ RoleSlug::label($role_slug) }}</span>
        </section>
    </section>

    @if($embedded)
        <section class="flex flex-wrap gap-2 shrink-0 items-start">
            @if($userId)
                <button type="button" wire:click="showEmbeddedView" class="bf-btn-ghost btn-sm">Ver ficha</button>
            @endif
            <button type="button" wire:click="closeEmbedded" class="bf-btn-ghost btn-sm">Cerrar</button>
        </section>
    @endif

    <x-avatar.crop-dialog />
</header>

@error('avatar')
    <p class="text-xs text-red-600 -mt-2 mb-3">{{ $message }}</p>
@enderror
