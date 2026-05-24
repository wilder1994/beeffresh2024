<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreCintaTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_cinta_when_products_marked_for_cinta_exist(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create([
            'show_on_cinta' => true,
            'image' => 'products/demo.jpg',
            'stock' => 50,
        ]);

        Storage::disk('public')->put('products/demo.jpg', 'fake');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('bf-cinta-marquee', false)
            ->assertSee($product->name);
    }

    public function test_home_hides_cinta_without_eligible_tiles(): void
    {
        Product::factory()->create([
            'show_on_cinta' => false,
            'image' => null,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('bf-cinta-marquee', false);
    }
}
