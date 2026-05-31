<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalog\CartStorage;
use App\Services\Payments\PaymentWebhookProcessor;
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
        config([
            'app.url' => 'https://tunnel.example.ngrok-free.app',
            'app.local_url' => 'http://localhost:8080',
            'app.env' => 'local',
        ]);
    }

    public function test_cart_persisted_on_localhost_is_available_on_ngrok_host(): void
    {
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

        $this->actingAs($customer);
        app(CartStorage::class)->put($cart);

        $this->assertNotEmpty(Cache::get('cart.user.'.$customer->id));

        $this->actingAs($customer)
            ->get('https://tunnel.example.ngrok-free.app/checkout')
            ->assertOk();

        $stored = app(CartStorage::class)->get();
        $this->assertArrayHasKey("product:{$product->id}:kg", $stored);
    }

    public function test_stale_localhost_session_does_not_restore_cart_after_webhook_clears_cache(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $product = Product::factory()->create(['stock' => 10, 'price_per_lb' => 10000]);

        $cart = [
            "product:{$product->id}:lb" => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'lb',
                'cantidad' => 2,
            ],
        ];

        app(CartStorage::class)->forgetForUser($customer->id);
        $this->actingAs($customer);
        app(CartStorage::class)->put($cart);

        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-CART-CLEAR',
            'amount' => '20000.00',
            'amount_in_cents' => 2000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Processing,
            'metadata' => [],
        ]);

        app(PaymentWebhookProcessor::class)->applyPaymentStatus(
            $payment,
            PaymentStatus::Approved,
        );

        $this->assertSame([], Cache::get('cart.user.'.$customer->id));

        $this->actingAs($customer)
            ->withSession(['carrito' => $cart])
            ->get('http://localhost:8080/')
            ->assertOk();

        $this->assertSame([], app(CartStorage::class)->get());
    }
}
