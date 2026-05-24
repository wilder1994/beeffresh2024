<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationType;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

final class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (NotificationType::cases() as $type) {
            NotificationTemplate::query()->updateOrCreate(
                [
                    'type' => $type,
                    'channel' => NotificationChannel::Email,
                ],
                [
                    'subject' => $type->label().' · BEEF FRESH',
                    'view' => 'emails.notifications.generic',
                    'is_active' => true,
                ],
            );
        }
    }
}
