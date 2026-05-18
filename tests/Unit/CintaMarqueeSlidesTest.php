<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CintaSlide;
use App\Support\CintaMarqueeSlides;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CintaMarqueeSlidesTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_slide_expands_to_fifteen_tiles(): void
    {
        $slide = CintaSlide::factory()->create(['sort_order' => 0]);

        $segment = CintaMarqueeSlides::expandSegment(collect([$slide]));

        $this->assertCount(15, $segment);
        $this->assertTrue($segment->every(fn (CintaSlide $s) => $s->is($slide)));
    }

    public function test_two_slides_expand_to_at_least_fifteen_tiles(): void
    {
        $a = CintaSlide::factory()->create(['sort_order' => 0]);
        $b = CintaSlide::factory()->create(['sort_order' => 1]);

        $segment = CintaMarqueeSlides::expandSegment(collect([$a, $b]));

        $this->assertCount(15, $segment);
        $this->assertSame(8, $segment->filter(fn (CintaSlide $s) => $s->is($a))->count());
        $this->assertSame(7, $segment->filter(fn (CintaSlide $s) => $s->is($b))->count());
    }

    public function test_track_duplicates_segment_for_seamless_loop(): void
    {
        $slide = CintaSlide::factory()->create();

        $track = CintaMarqueeSlides::track(collect([$slide]));

        $this->assertCount(30, $track);
    }
}
