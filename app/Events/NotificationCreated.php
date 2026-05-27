<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Notification;
use App\Support\NotificationActionUrl;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Inbox interno creado — preparado para campana realtime (Fase 1).
 */
class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Notification $notification,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->notification->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $payload = $this->notification->payload ?? [];

        return [
            'notification' => [
                'id' => $this->notification->id,
                'type' => $this->notification->type->value,
                'type_label' => $this->notification->type->label(),
                'title' => $this->notification->title,
                'body' => $this->notification->body,
                'payload' => $payload,
                'action_url' => NotificationActionUrl::normalize($payload['action_url'] ?? null),
                'read' => ! $this->notification->isUnread(),
                'read_url' => route('notifications.read', $this->notification),
                'read_at' => $this->notification->read_at?->toIso8601String(),
                'created_at' => $this->notification->created_at?->toIso8601String(),
                'created_human' => $this->notification->created_at?->diffForHumans(short: true),
            ],
            'unread_count' => Notification::query()
                ->where('user_id', $this->notification->user_id)
                ->whereNull('read_at')
                ->count(),
        ];
    }
}
