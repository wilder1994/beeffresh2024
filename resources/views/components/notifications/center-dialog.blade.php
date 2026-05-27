@auth
    <x-account.dialog name="notification-center" maxWidth="3xl" zIndex="z-[60]">
        <div
            class="bf-notification-center space-y-4"
            data-notification-center
            data-history-url="{{ route('notifications.history') }}"
            data-mark-all-url="{{ route('notifications.mark-all-read') }}"
            data-index-url="{{ route('notifications.index') }}"
        >
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 pr-8">
                <div>
                    <h2 class="text-lg font-bold text-stone-900">Centro de notificaciones</h2>
                    <p class="text-sm text-stone-600 mt-0.5">Historial completo y preferencias de alertas.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <button
                        type="button"
                        class="bf-btn-ghost text-sm"
                        data-notification-center-mark-all
                    >
                        Marcar todas como leídas
                    </button>
                    <a
                        href="{{ route('notifications.index') }}"
                        class="text-xs text-stone-500 hover:text-[var(--bf-brand)] hover:underline"
                    >
                        Abrir página completa
                    </a>
                </div>
            </div>

            @include('notifications.partials.preferences')

            <section>
                <h3 class="text-sm font-semibold text-stone-800 mb-3">Historial</h3>
                <div data-notification-center-list class="bf-notification-list min-h-[4rem]">
                    <div class="bf-store-empty text-sm">Cargando historial…</div>
                </div>
                <div class="mt-4 flex justify-center" data-notification-center-pagination hidden>
                    <button type="button" class="bf-btn-ghost text-sm" data-notification-center-load-more>
                        Cargar más
                    </button>
                </div>
            </section>
        </div>
    </x-account.dialog>
@endauth
