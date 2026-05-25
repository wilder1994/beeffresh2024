<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\DataTransferObjects\Notifications\CreateNotificationData;
use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationType;
use App\Jobs\Notifications\DispatchNotificationDeliveryJob;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Notifications\NotificationRepository;
use App\Services\Notifications\Channels\EmailNotificationChannel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $repository,
        private readonly NotificationChannelResolver $channelResolver,
        private readonly NotificationContentBuilder $contentBuilder,
        private readonly NotificationRecipientResolver $recipientResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<NotificationChannel>|null  $channels
     */
    public function notifyType(
        NotificationType $type,
        array $payload,
        ?array $channels = null,
    ): void {
        $audiences = config('notifications.types.'.$type->value.'.audiences', []);
        $recipients = $this->recipientResolver->forAudiences($audiences, $payload);

        if ($recipients->isEmpty() && isset($payload['recipients']) && $payload['recipients'] instanceof Collection) {
            $recipients = $payload['recipients'];
        }

        if ($recipients->isEmpty()) {
            Log::channel('single')->warning('Notification skipped: no recipients', [
                'type' => $type->value,
            ]);

            return;
        }

        $this->dispatch(new CreateNotificationData(
            type: $type,
            recipients: $recipients,
            payload: $payload,
            channels: $channels,
        ));
    }

    public function dispatch(CreateNotificationData $data): void
    {
        foreach ($data->recipientCollection() as $user) {
            $content = $this->contentBuilder->build($data->type, $data->payload, $user);
            $this->dispatchToUser($user, $data, $content);
        }
    }

    private function dispatchToUser(User $user, CreateNotificationData $data, $content): void
    {
        $channels = $this->channelResolver->resolve($data->type, $user, $data->channels);

        if ($channels === []) {
            return;
        }

        $inbox = null;
        if (in_array(NotificationChannel::Internal, $channels, true)) {
            $inbox = $this->repository->createInboxNotification(
                user: $user,
                type: $data->type,
                title: $content->title,
                body: $content->body,
                payload: array_merge($data->payload, [
                    'action_url' => $content->actionUrl,
                    'action_label' => $content->actionLabel,
                ]),
                metadata: $data->metadata,
            );
        }

        foreach ($channels as $channel) {
            $delivery = $this->repository->createDelivery(
                notification: $channel === NotificationChannel::Internal ? $inbox : null,
                user: $user,
                type: $data->type,
                channel: $channel,
                recipient: $this->resolveRecipientAddress($user, $channel),
                payload: $data->payload,
                metadata: $data->metadata,
            );

            $queue = $channel === NotificationChannel::Email
                ? config('notifications.queues.email')
                : config('notifications.queues.default');

            DispatchNotificationDeliveryJob::dispatch($delivery->id)
                ->onQueue($queue);
        }
    }

    private function resolveRecipientAddress(User $user, NotificationChannel $channel): ?string
    {
        return match ($channel) {
            NotificationChannel::Internal => (string) $user->id,
            NotificationChannel::Email => EmailNotificationChannel::resolveRecipient($user),
            NotificationChannel::WhatsApp, NotificationChannel::Sms => $user->phone,
            NotificationChannel::Push => (string) $user->id,
        };
    }
}
