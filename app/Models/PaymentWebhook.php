<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentWebhookStatus;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhook extends Model
{
    protected $fillable = [
        'gateway',
        'event_type',
        'idempotency_key',
        'payload',
        'signature',
        'checksum_valid',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'gateway' => PaymentGateway::class,
        'payload' => 'array',
        'checksum_valid' => 'boolean',
        'status' => PaymentWebhookStatus::class,
        'processed_at' => 'datetime',
    ];
}
