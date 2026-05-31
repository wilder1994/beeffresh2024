<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Models\Product;
use App\Models\User;
use App\Services\Catalog\CartStorage;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CartStorageCrossHostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_cart_persisted_on_localhost_is_available_on_ngrok_host(): void
    {
        config([
            'app.url' => 'https://tunnel.example.ngrok-free.app',
            'app.env' => 'local',
        ]);

        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $product = Product::factory()->create(['stock' => 10]);

        $cart = [
            "product:{$product->id}:kg" => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'kg',
                'cantidad' => 2,
                'precio' => 1000,
                'nombre' => $product->name,
            ],
        ];

        $this->actingAs($customer)
            ->withSession(['carrito' => $cart])
            ->get('http://localhost:8080/carrito')
            ->assertOk();

        $this->assertNotEmpty(Cache::get('cart.user.'.$customer->id));

        $this->actingAs($customer)
            ->get('https://tunnel.example.ngrok-free.app/checkout')
            ->assertOk();

        $stored = app(CartStorage::class)->get();
        $this->assertArrayHasKey("product:{$product->id}:kg", $stored);
    }
}
