@props([
    'user' => null,
    'mode' => 'view',
    'context' => 'admin',
    'editableAvatar' => false,
    'avatarFormId' => 'profile-update-form',
    'compactHeader' => false,
])

@php
    use App\Domain\Users\RoleSlug;
    $roleSlug = $user?->primaryRoleSlug();
    $displayName = $user
        ? trim($user->first_name.' '.$user->last_name)
        : ($mode === 'create' ? 'Nuevo usuario' : 'Usuario');
@endphp

@if($compactHeader)
    @isset($actions)
        <header class="flex flex-wrap justify-end gap-2 border-b border-stone-200 pb-4 mb-4">
            {{ $actions }}
        </header>
    @endisset
@else
    <header class="flex flex-wrap items-start gap-4 border-b border-stone-200 pb-4">
        @if($editableAvatar && $user)
            @include('profile.partials.avatar-field', ['formId' => $avatarFormId])
        @elseif($user)
            <x-user-avatar :user="$user" size="h-16 w-16" class="ring-2 ring-[var(--bf-brand)]/25 shrink-0" />
        @else
            <section class="h-16 w-16 rounded-full bg-stone-100 ring-2 ring-stone-200 flex items-center justify-center shrink-0" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </section>
        @endif

        <section class="min-w-0 flex-1">
            <h2 class="text-lg font-bold text-stone-900 truncate">{{ $displayName }}</h2>
            @if($user)
                <p class="text-sm text-stone-600 truncate">{{ $user->email }}</p>
                @if($user->phone)
                    <p class="text-sm text-stone-500">{{ $user->phone }}</p>
                @endif
            @endif
            <section class="flex flex-wrap gap-1.5 mt-2">
                @if($roleSlug)
                    <span class="badge badge-sm border border-stone-200 bg-stone-100 text-stone-700">{{ RoleSlug::label($roleSlug) }}</span>
                    <span class="badge badge-sm badge-ghost text-stone-600">{{ RoleSlug::audienceLabel($roleSlug) }}</span>
                @endif
                @if($user)
                    <span @class([
                        'badge badge-sm',
                        'bg-green-100 text-green-800 border-green-200' => $user->status === 'active',
                        'bg-stone-100 text-stone-600 border-stone-200' => $user->status !== 'active',
                    ])>{{ $user->status === 'active' ? 'Activo' : 'Inactivo' }}</span>
                @endif
            </section>
        </section>

        @isset($actions)
            <section class="flex flex-wrap gap-2 shrink-0 items-start">{{ $actions }}</section>
        @endisset
    </header>
@endif
