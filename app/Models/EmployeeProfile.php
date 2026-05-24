<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'position_id',
        'hire_date',
        'salary',
        'eps',
        'arl',
        'emergency_contact',
        'emergency_phone',
        'home_address',
        'home_neighborhood',
        'home_city',
        'home_state',
        'home_country',
        'home_latitude',
        'home_longitude',
        'notes',
        'vehicle_type',
        'plate_number',
        'driver_license',
        'license_expiration',
        'available',
        'assigned_zone',
        'average_rating',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'license_expiration' => 'date',
        'salary' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'available' => 'boolean',
        'home_latitude' => 'decimal:7',
        'home_longitude' => 'decimal:7',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function isDeliveryRole(): bool
    {
        return $this->position?->isDelivery() ?? false;
    }

    public function isDispatchRole(): bool
    {
        return $this->position?->isDispatch() ?? false;
    }
}
