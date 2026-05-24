<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'uuid',
        'order_id',
        'user_id',
        'gateway',
        'transaction_id',
        'reference',
        'amount',
        'amount_in_cents',
        'currency',
        'status',
        'payment_method',
        'gateway_response',
        'metadata',
        'paid_at',
        'failed_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_in_cents' => 'integer',
        'status' => PaymentStatus::class,
        'gateway' => PaymentGateway::class,
        'gateway_response' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            if ($payment->uuid === null) {
                $payment->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class)->latest();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function canProcessCheckout(): bool
    {
        if ($this->status === PaymentStatus::Approved) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        return in_array($this->status, [
            PaymentStatus::PendingPayment,
            PaymentStatus::Processing,
        ], true);
    }
}
