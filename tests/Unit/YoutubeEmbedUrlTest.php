<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\YoutubeEmbedUrl;
use PHPUnit\Framework\TestCase;

class YoutubeEmbedUrlTest extends TestCase
{
    public function test_resolves_watch_url(): void
    {
        $u = YoutubeEmbedUrl::resolve('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $u);
    }

    public function test_resolves_youtu_be(): void
    {
        $u = YoutubeEmbedUrl::resolve('https://youtu.be/dQw4w9WgXcQ');
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $u);
    }

    public function test_resolves_embed_url(): void
    {
        $u = YoutubeEmbedUrl::resolve('https://www.youtube.com/embed/dQw4w9WgXcQ');
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $u);
    }

    public function test_returns_null_for_invalid(): void
    {
        $this->assertNull(YoutubeEmbedUrl::resolve('https://example.com'));
    }
}
