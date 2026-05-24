<?php

declare(strict_types=1);

namespace App\Contracts\Notifications;

use App\DataTransferObjects\Notifications\ChannelDeliveryResult;
use App\DataTransferObjects\Notifications\NotificationContent;
use App\Enums\Notifications\NotificationChannel;
use App\Models\NotificationDelivery;

interface NotificationChannelInterface
{
    public function channel(): NotificationChannel;

    public function send(NotificationDelivery $delivery, NotificationContent $content): ChannelDeliveryResult;
}
