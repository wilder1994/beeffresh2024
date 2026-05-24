<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeliveryProofType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryProof extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'file_path',
        'notes',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'type' => DeliveryProofType::class,
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
