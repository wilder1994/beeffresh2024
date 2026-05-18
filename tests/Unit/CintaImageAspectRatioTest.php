<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Rules\CintaImageAspectRatio;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CintaImageAspectRatioTest extends TestCase
{
    public function test_accepts_exact_sixteen_by_nine(): void
    {
        $file = UploadedFile::fake()->image('ok.jpg', 1920, 1080);

        $this->assertTrue($this->validate($file));
    }

    public function test_accepts_gemini_like_dimensions_within_margin(): void
    {
        $file = UploadedFile::fake()->image('pollo.png', 1376, 768);

        $this->assertTrue($this->validate($file));
    }

    public function test_rejects_too_small(): void
    {
        $file = UploadedFile::fake()->image('small.jpg', 800, 450);

        $this->assertFalse($this->validate($file));
    }

    public function test_rejects_far_from_sixteen_by_nine(): void
    {
        $file = UploadedFile::fake()->image('wide.jpg', 1920, 400);

        $this->assertFalse($this->validate($file));
    }

    private function validate(UploadedFile $file): bool
    {
        return Validator::make(
            ['imagen' => $file],
            ['imagen' => [new CintaImageAspectRatio]],
        )->passes();
    }
}
