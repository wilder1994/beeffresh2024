<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\StockUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferItem extends Model
{
    protected $fillable = [
        'offer_id',
        'product_id',
        'quantity',
        'sale_unit',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'sale_unit' => StockUnit::class,
        'sort_order' => 'integer',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
