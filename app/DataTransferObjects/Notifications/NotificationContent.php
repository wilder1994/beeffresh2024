<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Notifications;

use App\Enums\Notifications\NotificationType;

final readonly class NotificationContent
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public NotificationType $type,
        public string $title,
        public string $body,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public array $payload = [],
    ) {}
}
