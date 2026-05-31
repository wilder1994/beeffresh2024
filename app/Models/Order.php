<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'courier_id',
        'handled_by_user_id',
        'total',
        'status',
        'payment_method',
        'delivery_attempt',
        'redelivery_fee',
        'shipping_recipient_name',
        'shipping_phone',
        'shipping_document_number',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'shipping_notes',
        'shipping_latitude',
        'shipping_longitude',
        'assigned_at',
        'handled_at',
        'ready_at',
        'picked_up_at',
        'delivered_at',
        'tracking_token',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'redelivery_fee' => 'decimal:2',
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
        'delivery_attempt' => 'integer',
        'shipping_latitude' => 'decimal:7',
        'shipping_longitude' => 'decimal:7',
        'assigned_at' => 'datetime',
        'handled_at' => 'datetime',
        'ready_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->latest();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OrderAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(OrderAssignment::class)->where('is_active', true);
    }

    public function deliveryProofs(): HasMany
    {
        return $this->hasMany(DeliveryProof::class);
    }

    public static function generateTrackingToken(): string
    {
        do {
            $token = Str::lower(Str::random(32));
        } while (static::query()->where('tracking_token', $token)->exists());

        return $token;
    }

    public function isActiveForCourier(): bool
    {
        if ($this->courier_id === null) {
            return false;
        }

        return in_array($this->status, OrderStatus::activeCourierStatuses(), true);
    }

    /** @param Builder<Order> $query */
    public function scopeForCourier(Builder $query, User $courier): Builder
    {
        return $query->where('courier_id', $courier->id);
    }

    /** @param Builder<Order> $query */
    public function scopeActiveForOperations(Builder $query): Builder
    {
        return $query->whereNotIn('status', array_map(
            static fn (OrderStatus $status): string => $status->value,
            OrderStatus::terminalStatuses()
        ));
    }

    /** @param Builder<Order> $query @param OrderStatus|list<OrderStatus> $status */
    public function scopeWithStatus(Builder $query, OrderStatus|array $status): Builder
    {
        $values = is_array($status)
            ? array_map(static fn (OrderStatus $s): string => $s->value, $status)
            : [$status->value];

        return $query->whereIn('status', $values);
    }

    /** @param Builder<Order> $query */
    public function scopeForHandledBy(Builder $query, User|int $dispatcher): Builder
    {
        $id = $dispatcher instanceof User ? $dispatcher->id : $dispatcher;

        return $query->where('handled_by_user_id', $id);
    }

    /** @param Builder<Order> $query */
    public function scopeAssignedToCourier(Builder $query, int $courierId): Builder
    {
        return $query->where('courier_id', $courierId);
    }
}
