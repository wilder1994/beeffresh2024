<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Contracts\Notifications\NotificationChannelInterface;
use App\Enums\Notifications\NotificationChannel;
use App\Services\Notifications\Channels\EmailNotificationChannel;
use App\Services\Notifications\Channels\InternalNotificationChannel;
use App\Services\Notifications\Channels\PushNotificationChannel;
use App\Services\Notifications\Channels\SmsNotificationChannel;
use App\Services\Notifications\Channels\WhatsAppNotificationChannel;
use InvalidArgumentException;

final class NotificationChannelManager
{
    /**
     * @return NotificationChannelInterface
     */
    public function driver(NotificationChannel $channel): NotificationChannelInterface
    {
        return match ($channel) {
            NotificationChannel::Internal => app(InternalNotificationChannel::class),
            NotificationChannel::Email => app(EmailNotificationChannel::class),
            NotificationChannel::WhatsApp => app(WhatsAppNotificationChannel::class),
            NotificationChannel::Push => app(PushNotificationChannel::class),
            NotificationChannel::Sms => app(SmsNotificationChannel::class),
            default => throw new InvalidArgumentException("Canal no registrado: {$channel->value}"),
        };
    }
}
