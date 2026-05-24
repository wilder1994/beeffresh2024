<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Notifications;

use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationType;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class CreateNotificationData
{
    /**
     * @param  Collection<int, User>|list<User>  $recipients
     * @param  array<string, mixed>  $payload
     * @param  list<NotificationChannel>|null  $channels
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public NotificationType $type,
        public Collection|array $recipients,
        public array $payload = [],
        public ?array $channels = null,
        public array $metadata = [],
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function recipientCollection(): Collection
    {
        return $this->recipients instanceof Collection
            ? $this->recipients
            : collect($this->recipients);
    }
}
