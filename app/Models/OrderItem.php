<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\StockUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'sale_unit',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'sale_unit' => StockUnit::class,
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
