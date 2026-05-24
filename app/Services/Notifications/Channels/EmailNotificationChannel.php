<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Contracts\Notifications\NotificationChannelInterface;
use App\DataTransferObjects\Notifications\ChannelDeliveryResult;
use App\DataTransferObjects\Notifications\NotificationContent;
use App\Enums\Notifications\NotificationChannel;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class EmailNotificationChannel implements NotificationChannelInterface
{
    public function channel(): NotificationChannel
    {
        return NotificationChannel::Email;
    }

    public function send(NotificationDelivery $delivery, NotificationContent $content): ChannelDeliveryResult
    {
        $recipient = $delivery->recipient;

        if ($recipient === null || $recipient === '') {
            return ChannelDeliveryResult::failed('Email recipient missing.');
        }

        $template = NotificationTemplate::query()
            ->where('type', $content->type)
            ->where('channel', NotificationChannel::Email)
            ->where('is_active', true)
            ->first();

        $view = $template?->view ?? 'emails.notifications.generic';
        $subject = $template?->subject ?? $content->title;

        try {
            Mail::send($view, [
                'content' => $content,
                'delivery' => $delivery,
                'user' => $delivery->user,
            ], function ($message) use ($recipient, $subject): void {
                $from = config('notifications.email.from');
                $message->to($recipient)
                    ->subject($subject)
                    ->from($from['address'], $from['name']);
            });

            return ChannelDeliveryResult::sent(['view' => $view]);
        } catch (\Throwable $e) {
            return ChannelDeliveryResult::failed($e->getMessage());
        }
    }

    public static function resolveRecipient(?User $user): ?string
    {
        return $user?->email;
    }
}
