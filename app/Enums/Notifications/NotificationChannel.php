<?php

declare(strict_types=1);

namespace App\Enums\Notifications;

enum NotificationChannel: string
{
    case Internal = 'internal';
    case Email = 'email';
    case WhatsApp = 'whatsapp';
    case Push = 'push';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Centro interno',
            self::Email => 'Correo electrónico',
            self::WhatsApp => 'WhatsApp',
            self::Push => 'Push',
            self::Sms => 'SMS',
        };
    }

    public function isImplemented(): bool
    {
        return match ($this) {
            self::Internal, self::Email => true,
            default => false,
        };
    }
}
