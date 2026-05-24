<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Notifications;

use App\Enums\Notifications\NotificationChannel;

final readonly class ChannelDeliveryResult
{
    public function __construct(
        public bool $success,
        public ?string $errorMessage = null,
        public array $metadata = [],
    ) {}

    public static function sent(array $metadata = []): self
    {
        return new self(true, metadata: $metadata);
    }

    public static function failed(string $message, array $metadata = []): self
    {
        return new self(false, $message, $metadata);
    }

    public static function skipped(string $reason): self
    {
        return new self(false, $reason, ['skipped' => true]);
    }
}
