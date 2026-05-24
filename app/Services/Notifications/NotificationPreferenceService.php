<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;

final class NotificationPreferenceService
{
    public function isEnabled(User $user, NotificationChannel $channel, ?NotificationType $type = null): bool
    {
        $specific = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->where('type', $type)
            ->first();

        if ($specific !== null) {
            return (bool) $specific->enabled;
        }

        $global = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->whereNull('type')
            ->first();

        if ($global !== null) {
            return (bool) $global->enabled;
        }

        return true;
    }

    public function setPreference(
        User $user,
        NotificationChannel $channel,
        bool $enabled,
        ?NotificationType $type = null,
    ): NotificationPreference {
        return NotificationPreference::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'channel' => $channel,
                'type' => $type,
            ],
            ['enabled' => $enabled],
        );
    }

    /**
     * @return list<array{channel: NotificationChannel, enabled: bool, type: NotificationType|null}>
     */
    public function listForUser(User $user): array
    {
        $channels = [NotificationChannel::Internal, NotificationChannel::Email, NotificationChannel::Push];
        $result = [];

        foreach ($channels as $channel) {
            $result[] = [
                'channel' => $channel,
                'enabled' => $this->isEnabled($user, $channel),
                'type' => null,
            ];
        }

        return $result;
    }
}
