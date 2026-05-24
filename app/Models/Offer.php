<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Offer extends Model
{
    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'image',
        'offer_price',
        'product_id',
        'volume_min_quantity',
        'volume_sale_unit',
        'volume_offer_price_kg',
        'volume_offer_price_lb',
        'is_active',
        'show_on_cinta',
        'show_on_home',
        'sort_order',
    ];

    protected $casts = [
        'type' => OfferType::class,
        'volume_sale_unit' => StockUnit::class,
        'offer_price' => 'decimal:2',
        'volume_min_quantity' => 'decimal:2',
        'volume_offer_price_kg' => 'decimal:2',
        'volume_offer_price_lb' => 'decimal:2',
        'is_active' => 'boolean',
        'show_on_cinta' => 'boolean',
        'show_on_home' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfferItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): string
    {
        return asset('storage/offers/'.$this->image);
    }

    public function deleteImageFromDisk(): void
    {
        if ($this->image !== '' && Storage::disk('public')->exists('offers/'.$this->image)) {
            Storage::disk('public')->delete('offers/'.$this->image);
        }
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isBundle(): bool
    {
        return $this->type === OfferType::Bundle;
    }

    public function isVolume(): bool
    {
        return $this->type === OfferType::Volume;
    }
}
