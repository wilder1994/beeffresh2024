<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    public const SLUG_DELIVERY = 'domiciliario';

    public const SLUG_DISPATCH = 'despachador';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(EmployeeProfile::class, 'position_id');
    }

    public function isDelivery(): bool
    {
        return $this->slug === self::SLUG_DELIVERY;
    }

    public function isDispatch(): bool
    {
        return $this->slug === self::SLUG_DISPATCH;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
