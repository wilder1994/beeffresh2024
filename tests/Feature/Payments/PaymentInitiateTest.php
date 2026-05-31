<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentInitiateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_customer_can_initiate_checkout_payment(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $product = Product::factory()->create(['stock' => 5, 'price_per_lb' => 18000]);

        $cart = [
            "product:{$product->id}:lb" => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'lb',
                'cantidad' => 1,
            ],
        ];

        $this->actingAs($customer)
            ->withSession(['carrito' => $cart])
            ->post(route('payments.initiate'), [
                'gateway' => 'wompi',
            ])
            ->assertRedirect()
            ->assertRedirectContains('/pago/procesar/');
    }
}
