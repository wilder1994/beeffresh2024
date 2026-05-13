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
    ];

    protected $casts = [
        'accepts_promotions' => 'boolean',
        'loyalty_points' => 'integer',
        'balance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
