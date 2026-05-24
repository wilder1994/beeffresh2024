<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DataTransferObjects\Store\CintaTile;
use App\Support\CintaMarqueeSlides;
use Tests\TestCase;

class CintaMarqueeSlidesTest extends TestCase
{
    public function test_expand_segment_repeats_single_tile_to_minimum(): void
    {
        $tile = new CintaTile(
            url: '/producto',
            imageUrl: 'https://example.com/img.jpg',
            title: 'Lomo',
            badge: 'Promo',
        );

        $segment = CintaMarqueeSlides::expandSegment(collect([$tile]));

        $this->assertGreaterThanOrEqual(15, $segment->count());
        $this->assertTrue($segment->every(fn (CintaTile $t) => $t->title === 'Lomo'));
    }

    public function test_track_duplicates_segment_for_infinite_loop(): void
    {
        $tiles = collect([
            new CintaTile('/', 'https://example.com/a.jpg', 'A', 'Pack'),
            new CintaTile('/', 'https://example.com/b.jpg', 'B', 'Promo'),
        ]);

        $track = CintaMarqueeSlides::track($tiles);
        $segment = CintaMarqueeSlides::expandSegment($tiles);

        $this->assertSame($segment->count() * 2, $track->count());
    }

    public function test_animation_duration_scales_with_tile_count(): void
    {
        $tile = new CintaTile('/', 'https://example.com/x.jpg', 'X', 'Producto');
        $duration = CintaMarqueeSlides::animationDurationSeconds(collect([$tile]));

        $this->assertGreaterThanOrEqual(24, $duration);
    }
}
