@props([
    'variant' => 'dark',
    'placement' => 'bottom',
    'align' => 'end',
    'compact' => false,
])

@php
    use App\Repositories\Notifications\NotificationRepository;

    $panelPositionClass = match ($placement) {
        'top' => 'bottom-full mb-2',
        'sidebar' => 'bottom-full mb-2 left-0 w-[min(18rem,calc(100vw-1.5rem))]',
        'aside' => 'left-full ml-2 bottom-0 w-72 max-w-[calc(100vw-2rem)]',
        default => 'top-full mt-2',
    };

    $panelAlignClass = match ($align) {
        'stretch' => 'left-0 right-0',
        'start' => 'left-0 right-auto',
        default => 'right-0 left-auto',
    };

    $panelWidthClass = match ($placement) {
        'sidebar', 'aside' => '',
        default => $align === 'stretch' ? 'w-full' : 'w-80',
    };

    $bellGradientId = 'bf-bell-' . uniqid();
    $iconSizeClass = $compact ? 'h-5 w-5' : 'h-6 w-6';

    $bellGradient = $variant === 'light'
        ? ['#e8a820', '#c8933a', '#8b5a14']
        : ['#ffe566', '#f0b429', '#b8862d'];

    $unreadCount = auth()->check()
        ? app(NotificationRepository::class)->unreadCount(auth()->user())
        : 0;
@endphp

<div
    class="relative shrink-0 overflow-visible"
    data-notification-bell
    data-feed-url="{{ route('notifications.feed') }}"
    data-index-url="{{ route('notifications.index') }}"
    data-mark-all-url="{{ route('notifications.mark-all-read') }}"
>
    <span
        data-notification-count
        @class([
            'bf-notification-badge pointer-events-none absolute z-20 min-w-[1.125rem] h-[1.125rem] px-1 rounded-full bg-[var(--bf-crimson)] text-white text-[10px] font-bold leading-none items-center justify-center',
            'top-0 right-0 translate-x-1/3 -translate-y-1/3',
            $unreadCount > 0 ? 'inline-flex' : 'hidden',
        ])
    >{{ $unreadCount > 99 ? '99+' : ($unreadCount > 0 ? $unreadCount : '') }}</span>

    <details class="relative overflow-visible">
        <summary
            @class([
                'bf-notification-bell relative list-none [&::-webkit-details-marker]:hidden',
                'bf-notification-bell--' . $variant,
                $compact ? 'bf-notification-bell--compact' : 'bf-notification-bell--default',
            ])
            aria-label="Notificaciones"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                @class(['bf-notification-bell__icon', $iconSizeClass])
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <defs>
                    <linearGradient id="{{ $bellGradientId }}" x1="12" y1="2" x2="12" y2="22" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="{{ $bellGradient[0] }}" />
                        <stop offset="48%" stop-color="{{ $bellGradient[1] }}" />
                        <stop offset="100%" stop-color="{{ $bellGradient[2] }}" />
                    </linearGradient>
                </defs>
                <path
                    fill="url(#{{ $bellGradientId }})"
                    d="M12 2.25a.75.75 0 0 1 .75.75v.98c3.16.47 5.63 3.08 6.13 6.35.04.22.07.45.07.67v3.28l1.62 1.62a.75.75 0 1 1-1.06 1.06l-.18-.18H5.67l-.18.18a.75.75 0 0 1-1.06-1.06L6.05 14.53v-3.28c0-.22.03-.45.07-.67.5-3.27 2.97-5.88 6.13-6.35V3a.75.75 0 0 1 .75-.75Z"
                />
                <path
                    fill="{{ $bellGradient[2] }}"
                    d="M9.75 17.25h4.5a2.25 2.25 0 0 0-4.5 0Z"
                />
            </svg>
        </summary>

        <div @class(['bf-notification-dropdown absolute z-[250] rounded-xl border border-black/10 bg-white text-[var(--bf-ink)] shadow-xl overflow-hidden max-w-[calc(100vw-2rem)]', $panelPositionClass, $panelAlignClass, $panelWidthClass])>
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
            <div class="px-4 py-3 border-t border-stone-100 bg-stone-50 space-y-2">
                <label class="flex items-center gap-2 text-xs text-stone-600 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        class="checkbox checkbox-xs checkbox-primary"
                        data-notification-sound-toggle
                        checked
                        aria-label="Reproducir sonido al recibir notificaciones"
                    >
                    <span data-notification-sound-label>Sonido activo</span>
                </label>
                <a href="{{ route('notifications.index') }}" class="block text-sm font-medium text-[var(--bf-brand)] hover:underline">Ver todas</a>
            </div>
        </div>
    </details>
</div>

