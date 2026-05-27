@php
    $user = auth()->user();
    $layout = match (true) {
        $user?->isStaff() => 'layouts.app',
        $user?->isSupplier() => 'layouts.supplier',
        default => 'layouts.store',
    };
    $contentSection = $user?->isStaff() ? 'contenido' : 'content';
@endphp

@extends($layout)

@section('titulo', 'Notificaciones')

@section($contentSection)
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

    @include('notifications.partials.preferences', ['preferences' => $preferences])

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
                        <a href="{{ \App\Support\NotificationActionUrl::normalize($notification->payload['action_url']) }}" class="text-xs font-medium text-[var(--bf-brand)] hover:underline">Abrir</a>
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
