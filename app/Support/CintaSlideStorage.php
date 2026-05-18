<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class CintaSlideStorage
{
    public static function store(UploadedFile $file): string
    {
        $folder = config('cinta.storage_folder', 'cinta');
        $name = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();

        $file->storeAs($folder, $name, 'public');

        return $name;
    }

    public static function delete(?string $filename): void
    {
        if ($filename === null || $filename === '') {
            return;
        }

        $path = config('cinta.storage_folder', 'cinta').'/'.$filename;
        Storage::disk('public')->delete($path);
    }
}
