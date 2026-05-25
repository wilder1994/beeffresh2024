@extends(auth()->user()?->isStaff() ? 'layouts.app' : 'layouts.store')

@section('titulo', 'Notificaciones')

@if(! auth()->user()?->isStaff())
@section('content')
@else
@section('contenido')
@endif
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-stone-900">Centro de notificaciones</h1>
            <p class="text-sm text-stone-600 mt-0.5">Alertas operacionales y actualizaciones de pedidos.</p>
        </div>
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="bf-btn-ghost text-sm">Marcar todas como leídas</button>
        </form>
    </div>

    <section class="bf-store-panel p-5">
        <h2 class="text-sm font-semibold text-stone-800 mb-4">Preferencias</h2>
        <form method="POST" action="{{ route('notifications.preferences.update') }}" class="grid sm:grid-cols-3 gap-4">
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
    </section>

    <section class="bf-notification-list">
        @forelse($notifications as $notification)
            <article @class(['bf-notification-item', 'bf-notification-item--unread' => $notification->isUnread()])>
                <div class="bf-notification-item__body">
                    <p class="bf-notification-item__title">{{ $notification->title }}</p>
                    <p class="bf-notification-item__text">{{ $notification->body }}</p>
                    <p class="bf-notification-item__meta">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                <div class="bf-notification-item__actions">
                    @if($notification->payload['action_url'] ?? null)
                        <a href="{{ $notification->payload['action_url'] }}" class="text-xs font-medium text-[var(--bf-brand)] hover:underline">Abrir</a>
                    @endif
                    @if($notification->isUnread())
                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs text-stone-500 hover:text-stone-800">Marcar leída</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="bf-store-empty">No tienes notificaciones todavía.</div>
        @endforelse
    </section>

    {{ $notifications->links() }}
</div>
@endsection
