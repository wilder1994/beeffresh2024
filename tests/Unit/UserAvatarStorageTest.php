<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\UserAvatarStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserAvatarStorageTest extends TestCase
{
    public function test_replace_stores_new_file_and_deletes_old(): void
    {
        Storage::fake('public');

        $oldPath = 'avatars/old.jpg';
        Storage::disk('public')->put($oldPath, 'old');

        $file = UploadedFile::fake()->image('new.jpg');
        $newPath = UserAvatarStorage::replace($oldPath, $file);

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
    }
}
