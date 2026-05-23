<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\TaxonomyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeatCut extends Model
{
    use HasFactory;

    protected $fillable = [
        'meat_type_id',
        'name',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => TaxonomyStatus::class,
    ];

    public function meatType(): BelongsTo
    {
        return $this->belongsTo(MeatType::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
