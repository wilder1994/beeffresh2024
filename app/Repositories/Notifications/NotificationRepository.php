<?php

declare(strict_types=1);

namespace App\Repositories\Notifications;

use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationDeliveryStatus;
use App\Enums\Notifications\NotificationType;
use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class NotificationRepository
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function createInboxNotification(
        User $user,
        NotificationType $type,
        string $title,
        string $body,
        array $payload = [],
        array $metadata = [],
    ): Notification {
        $notification = Notification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'payload' => $payload,
            'metadata' => $metadata,
        ]);

        event(new NotificationCreated($notification));

        return $notification;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function createDelivery(
        ?Notification $notification,
        ?User $user,
        NotificationType $type,
        NotificationChannel $channel,
        ?string $recipient,
        array $payload = [],
        array $metadata = [],
    ): NotificationDelivery {
        return NotificationDelivery::query()->create([
            'notification_id' => $notification?->id,
            'user_id' => $user?->id,
            'type' => $type,
            'channel' => $channel,
            'recipient' => $recipient,
            'payload' => $payload,
            'status' => NotificationDeliveryStatus::Pending,
            'metadata' => $metadata,
        ]);
    }

    public function markDeliveryQueued(NotificationDelivery $delivery): void
    {
        $delivery->status = NotificationDeliveryStatus::Queued;
        $delivery->queued_at = now();
        $delivery->save();
    }

    public function markDeliverySent(NotificationDelivery $delivery, array $metadata = []): void
    {
        $delivery->status = NotificationDeliveryStatus::Sent;
        $delivery->sent_at = now();
        $delivery->metadata = array_merge($delivery->metadata ?? [], $metadata);
        $delivery->save();
    }

    public function markDeliveryFailed(NotificationDelivery $delivery, string $error, array $metadata = []): void
    {
        $delivery->status = NotificationDeliveryStatus::Failed;
        $delivery->failed_at = now();
        $delivery->error_message = $error;
        $delivery->metadata = array_merge($delivery->metadata ?? [], $metadata);
        $delivery->save();
    }

    public function markDeliverySkipped(NotificationDelivery $delivery, string $reason): void
    {
        $delivery->status = NotificationDeliveryStatus::Skipped;
        $delivery->failed_at = now();
        $delivery->error_message = $reason;
        $delivery->save();
    }

    public function incrementAttempt(NotificationDelivery $delivery): void
    {
        $delivery->attempt_count = ($delivery->attempt_count ?? 0) + 1;
        $delivery->save();
    }

    public function unreadCount(User $user): int
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function recentForUser(User $user, int $limit = 8): Collection
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    public function markAllAsRead(User $user): int
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
