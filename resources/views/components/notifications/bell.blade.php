@props([
    'variant' => 'dark',
])

<div
    class="relative shrink-0"
    data-notification-bell
    data-feed-url="{{ route('notifications.feed') }}"
    data-index-url="{{ route('notifications.index') }}"
    data-mark-all-url="{{ route('notifications.mark-all-read') }}"
>
    <details class="relative">
        <summary
            class="relative list-none [&::-webkit-details-marker]:hidden cursor-pointer rounded-full p-2 hover:bg-white/10 transition {{ $variant === 'light' ? 'text-[var(--bf-ink)] hover:bg-stone-100' : 'text-white' }}"
            aria-label="Notificaciones"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                data-notification-count
                class="bf-notification-badge hidden absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-[var(--bf-rust)] text-white text-[10px] font-bold leading-none flex items-center justify-center"
            ></span>
        </summary>

        <div class="bf-notification-dropdown absolute right-0 top-full z-[250] mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-xl border border-black/10 bg-white text-[var(--bf-ink)] shadow-xl overflow-hidden">
            <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-stone-100">
                <p class="text-sm font-semibold">Notificaciones</p>
                <form method="POST" action="{{ route('notifications.mark-all-read') }}" data-notification-mark-all>
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-xs text-[var(--bf-brand)] hover:underline">Marcar todas</button>
                </form>
            </div>
            <ul data-notification-list class="max-h-80 overflow-y-auto divide-y divide-stone-100">
                <li class="px-4 py-6 text-sm text-[var(--bf-muted)] text-center">Cargando…</li>
            </ul>
            <div class="px-4 py-3 border-t border-stone-100 bg-stone-50">
                <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-[var(--bf-brand)] hover:underline">Ver todas</a>
            </div>
        </div>
    </details>
</div>

@once
    @push('scripts')
        @vite('resources/js/notificationBell.js')
    @endpush
@endonce
