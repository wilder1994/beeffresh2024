<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NosotrosPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_nosotros_page(): void
    {
        $response = $this->get(route('nosotros'));

        $response->assertOk();
        $response->assertSee('Nosotros', false);
    }
}
