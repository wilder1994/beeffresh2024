<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectLocalPaymentFlowTest extends TestCase
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

    public function test_checkout_on_localhost_redirects_to_app_url(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();

        $response = $this->actingAs($customer)
            ->get('http://localhost:8080/checkout');

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('https://tunnel.example.ngrok-free.app/checkout', $location);
        $this->assertStringContainsString('bf_tunnel_handoff=', $location);
    }

    public function test_checkout_on_app_url_host_is_not_redirected(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $product = Product::factory()->create(['stock' => 5]);

        $cart = [
            "product:{$product->id}:kg" => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'kg',
                'cantidad' => 1,
            ],
        ];

        $this->actingAs($customer)
            ->withSession(['carrito' => $cart])
            ->get('https://tunnel.example.ngrok-free.app/checkout')
            ->assertOk();
    }
}
