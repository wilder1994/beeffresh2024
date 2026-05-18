<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CintaSlide extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
        'alt',
        'link_url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function imageUrl(): string
    {
        return asset('storage/'.config('cinta.storage_folder').'/'.$this->image);
    }

    public function deleteImageFile(): void
    {
        $path = config('cinta.storage_folder').'/'.$this->image;
        if ($this->image !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
