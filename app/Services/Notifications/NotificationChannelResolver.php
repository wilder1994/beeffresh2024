<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationType;
use App\Models\User;

final class NotificationChannelResolver
{
    public function __construct(
        private readonly NotificationPreferenceService $preferences,
    ) {}

    /**
     * @param  list<NotificationChannel>|null  $override
     * @return list<NotificationChannel>
     */
    public function resolve(NotificationType $type, User $user, ?array $override = null): array
    {
        if ($override !== null) {
            return $this->filterEnabled($override, $type, $user);
        }

        $configured = config('notifications.types.'.$type->value.'.channels', [
            NotificationChannel::Internal,
        ]);

        $channels = array_map(
            fn (NotificationChannel|string $channel): NotificationChannel => $channel instanceof NotificationChannel
                ? $channel
                : NotificationChannel::from((string) $channel),
            $configured,
        );

        return $this->filterEnabled($channels, $type, $user);
    }

    /**
     * @param  list<NotificationChannel>  $channels
     * @return list<NotificationChannel>
     */
    private function filterEnabled(array $channels, NotificationType $type, User $user): array
    {
        $resolved = [];

        foreach ($channels as $channel) {
            if (! $channel->isImplemented()) {
                continue;
            }

            if ($this->preferences->isEnabled($user, $channel, $type)) {
                $resolved[] = $channel;
            }
        }

        return array_values(array_unique($resolved, SORT_REGULAR));
    }
}
