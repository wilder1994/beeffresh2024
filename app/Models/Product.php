<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\ProductStatus;
use App\Domain\Catalog\SaleType;
use App\Domain\Catalog\StockUnit;
use App\Services\Catalog\ProductPromotionResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'meat_type_id',
        'meat_cut_id',
        'name',
        'slug',
        'sku',
        'description',
        'image',
        'status',
        'price_per_kg',
        'price_per_lb',
        'promo_price_kg',
        'promo_price_lb',
        'promo_start',
        'promo_end',
        'stock',
        'stock_unit',
        'min_stock',
        'sale_type',
        'featured',
        'show_on_cinta',
    ];

    protected $casts = [
        'status' => ProductStatus::class,
        'sale_type' => SaleType::class,
        'stock_unit' => StockUnit::class,
        'price_per_kg' => 'decimal:2',
        'price_per_lb' => 'decimal:2',
        'promo_price_kg' => 'decimal:2',
        'promo_price_lb' => 'decimal:2',
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'promo_start' => 'date',
        'promo_end' => 'date',
        'featured' => 'boolean',
        'show_on_cinta' => 'boolean',
    ];

    public function meatType(): BelongsTo
    {
        return $this->belongsTo(MeatType::class);
    }

    public function meatCut(): BelongsTo
    {
        return $this->belongsTo(MeatCut::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function imageUrl(): ?string
    {
        if ($this->image === null || $this->image === '') {
            return null;
        }

        return asset('storage/products/'.$this->image);
    }

    public function effectivePriceKg(): float
    {
        return app(ProductPromotionResolver::class)->effectivePriceKg($this);
    }

    public function effectivePriceLb(): float
    {
        return app(ProductPromotionResolver::class)->effectivePriceLb($this);
    }

    public function isOnPromotion(): bool
    {
        return app(ProductPromotionResolver::class)->isActive($this);
    }

    public function isLowStock(): bool
    {
        return $this->min_stock > 0 && $this->stock <= $this->min_stock;
    }

    public function isPurchasable(): bool
    {
        return $this->status === ProductStatus::Available && $this->stock > 0;
    }

    public function deleteImageFromDisk(): void
    {
        if ($this->image && Storage::disk('public')->exists('products/'.$this->image)) {
            Storage::disk('public')->delete('products/'.$this->image);
        }
    }
}
