@props([
    'user',
    'variant' => 'dark',
])

@php
    $ringClass = $variant === 'dark'
        ? 'ring-2 ring-white/40'
        : 'ring-2 ring-[var(--bf-brand)]/30';
@endphp

<details class="relative shrink-0">
    <summary
        @class(['rounded-full list-none [&::-webkit-details-marker]:hidden cursor-pointer block', $ringClass])
        aria-label="Menú de cuenta de {{ $user->name }}"
    >
        <x-user-avatar :user="$user" size="h-10 w-10" />
    </summary>
    <ul class="menu menu-sm absolute right-0 top-full z-[100] mt-2 w-52 rounded-box border border-black/10 bg-white p-2 text-gray-900 shadow-lg">
        <li class="px-3 py-1.5 text-xs text-stone-500 border-b border-stone-100 mb-1 truncate">{{ $user->name }}</li>
        <li><x-profile.open-button tag="a" class="block w-full rounded-lg px-3 py-2 hover:bg-stone-100 font-normal">Mi perfil</x-profile.open-button></li>
        @if($user->isCustomer())
            <li><a href="{{ route('customer.orders.index') }}" class="block w-full rounded-lg px-3 py-2 hover:bg-stone-100 font-normal">Mis pedidos</a></li>
        @endif
        <li>
            <button
                type="button"
                class="block w-full text-left rounded-lg px-3 py-2 hover:bg-stone-100 font-normal"
                data-open-notification-center
            >
                Notificaciones
            </button>
        </li>
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left rounded-lg px-3 py-2 hover:bg-stone-100">Cerrar sesión</button>
            </form>
        </li>
    </ul>
</details>
