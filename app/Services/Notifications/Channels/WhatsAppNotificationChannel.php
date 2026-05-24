<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Enums\Notifications\NotificationChannel;

final class WhatsAppNotificationChannel extends StubNotificationChannel
{
    public function channel(): NotificationChannel
    {
        return NotificationChannel::WhatsApp;
    }
}
