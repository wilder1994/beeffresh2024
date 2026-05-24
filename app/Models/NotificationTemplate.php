<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationType;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'type',
        'channel',
        'subject',
        'view',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'channel' => NotificationChannel::class,
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];
}
