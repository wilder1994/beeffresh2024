<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Contracts\Notifications\NotificationChannelInterface;
use App\DataTransferObjects\Notifications\ChannelDeliveryResult;
use App\DataTransferObjects\Notifications\NotificationContent;
use App\Enums\Notifications\NotificationChannel;
use App\Models\NotificationDelivery;

final class InternalNotificationChannel implements NotificationChannelInterface
{
    public function channel(): NotificationChannel
    {
        return NotificationChannel::Internal;
    }

    public function send(NotificationDelivery $delivery, NotificationContent $content): ChannelDeliveryResult
    {
        if ($delivery->notification_id === null) {
            return ChannelDeliveryResult::failed('No internal notification record linked.');
        }

        return ChannelDeliveryResult::sent([
            'notification_id' => $delivery->notification_id,
        ]);
    }
}
