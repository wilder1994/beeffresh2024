@php
    $preferences = $preferences ?? app(\App\Services\Notifications\NotificationPreferenceService::class)->listForUser(auth()->user());
@endphp

<section class="bf-store-panel p-5 space-y-4" data-notification-preferences-form>
    <h2 class="text-sm font-semibold text-stone-800">Preferencias</h2>
    <form
        method="POST"
        action="{{ route('notifications.preferences.update') }}"
        class="grid sm:grid-cols-3 gap-4"
        data-notification-preferences
    >
        @csrf
        @method('PATCH')
        @foreach($preferences as $pref)
            @if(in_array($pref['channel']->value, ['internal', 'email', 'push'], true))
                <label class="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="{{ $pref['channel']->value === 'internal' ? 'internal' : $pref['channel']->value }}_enabled"
                        value="1"
                        @checked($pref['enabled'])
                        @disabled(! $pref['channel']->isImplemented())
                        class="checkbox checkbox-sm checkbox-primary"
                    >
                    <span>{{ $pref['channel']->label() }}@unless($pref['channel']->isImplemented()) (próximamente)@endunless</span>
                </label>
            @endif
        @endforeach
        <div class="sm:col-span-3">
            <button type="submit" class="bf-btn-primary text-sm">Guardar preferencias</button>
        </div>
    </form>

    <div class="pt-4 border-t border-stone-100" data-notification-sound-prefs>
        <p class="text-xs font-medium text-stone-700 mb-2">Sonido en el navegador</p>
        <label class="flex items-center gap-2 text-sm cursor-pointer select-none">
            <input
                type="checkbox"
                class="checkbox checkbox-sm checkbox-primary"
                data-notification-sound-toggle
                checked
                aria-label="Reproducir sonido al recibir notificaciones nuevas"
            >
            <span data-notification-sound-label>Sonido activo</span>
        </label>
        <p class="text-xs text-stone-500 mt-1">El volumen lo controla tu sistema operativo y el navegador.</p>
    </div>
</section>
