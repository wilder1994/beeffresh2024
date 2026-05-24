<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentAttemptType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    protected $fillable = [
        'payment_id',
        'type',
        'status',
        'payload',
        'response',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'type' => PaymentAttemptType::class,
        'payload' => 'array',
        'response' => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
