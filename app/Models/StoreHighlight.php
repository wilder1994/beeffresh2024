<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StoreHighlight extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
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

        return asset('storage/store-highlights/'.$this->image);
    }

    public function deleteImageFromDisk(): void
    {
        if ($this->image && Storage::disk('public')->exists('store-highlights/'.$this->image)) {
            Storage::disk('public')->delete('store-highlights/'.$this->image);
        }
    }
}
