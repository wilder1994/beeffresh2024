<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'neighborhood',
        'city',
        'state',
        'address_reference',
        'delivery_notes',
        'accepts_promotions',
        'loyalty_points',
        'balance',
        'postal_code',
        'country',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'accepts_promotions' => 'boolean',
        'loyalty_points' => 'integer',
        'balance' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
