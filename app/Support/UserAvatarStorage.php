<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class UserAvatarStorage
{
    /**
     * Guarda el archivo nuevo y elimina el anterior solo si la subida fue exitosa.
     */
    public static function replace(?string $existingPath, UploadedFile $file): string
    {
        $newPath = $file->store('avatars', 'public');

        if ($existingPath !== null && $existingPath !== '' && $existingPath !== $newPath) {
            Storage::disk('public')->delete($existingPath);
        }

        return $newPath;
    }
}
