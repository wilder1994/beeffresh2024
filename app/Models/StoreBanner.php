<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StoreBanner extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'link',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function imageUrl(): ?string
    {
        if ($this->image === null || $this->image === '') {
            return null;
        }

        return asset('storage/store-banners/'.$this->image);
    }

    public function deleteImageFromDisk(): void
    {
        if ($this->image && Storage::disk('public')->exists('store-banners/'.$this->image)) {
            Storage::disk('public')->delete('store-banners/'.$this->image);
        }
    }
}
