<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Contracts\Notifications\NotificationChannelInterface;
use App\DataTransferObjects\Notifications\ChannelDeliveryResult;
use App\DataTransferObjects\Notifications\NotificationContent;
use App\Enums\Notifications\NotificationChannel;
use App\Models\NotificationDelivery;
use Illuminate\Support\Facades\Log;

abstract class StubNotificationChannel implements NotificationChannelInterface
{
    abstract public function channel(): NotificationChannel;

    public function send(NotificationDelivery $delivery, NotificationContent $content): ChannelDeliveryResult
    {
        Log::channel('single')->info('Notification channel stub (not implemented)', [
            'channel' => $this->channel()->value,
            'type' => $content->type->value,
            'delivery_id' => $delivery->id,
        ]);

        return ChannelDeliveryResult::skipped(
            sprintf('Canal %s preparado pero no implementado.', $this->channel()->label())
        );
    }
}
