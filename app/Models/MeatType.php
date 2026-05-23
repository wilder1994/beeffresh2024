<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\TaxonomyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeatType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'status',
    ];

    protected $casts = [
        'status' => TaxonomyStatus::class,
    ];

    public function meatCuts(): HasMany
    {
        return $this->hasMany(MeatCut::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
