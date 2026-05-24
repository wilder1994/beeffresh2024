<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);
    }

    public function test_poll_returns_approved_payload_and_clears_cart_session(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $product = Product::factory()->create(['stock' => 10, 'price_per_lb' => 10000]);

        $cart = [
            "product:{$product->id}:lb" => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'lb',
                'cantidad' => 2,
            ],
        ];

        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-POLL-001',
            'amount' => '20000.00',
            'amount_in_cents' => 2000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Approved,
            'transaction_id' => 'tx-approved',
            'metadata' => CheckoutSessionData::fromMetadata([
                'cart_snapshot' => $cart,
                'shipping' => $customer->snapshotShippingFromProfile(),
                'lines' => [],
                'subtotal' => 20000,
                'shipping_fee' => 0,
                'discount' => 0,
                'total' => 20000,
            ])->toMetadata(),
        ]);

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total' => '20000.00',
            'status' => OrderStatus::Pending,
            'payment_method' => PaymentMethod::Online,
            'tracking_token' => (string) \Illuminate\Support\Str::uuid(),
            'shipping_recipient_name' => 'Cliente Demo',
            'shipping_phone' => '3000000000',
            'shipping_address_line1' => 'Calle 1',
            'shipping_city' => 'Bogotá',
            'shipping_state' => 'Cundinamarca',
            'shipping_country' => 'CO',
        ]);
        $payment->order_id = $order->id;
        $payment->save();

        $this->actingAs($customer)
            ->withSession(['carrito' => $cart])
            ->getJson(route('payments.status', $payment->uuid))
            ->assertOk()
            ->assertJson([
                'status' => 'approved',
                'terminal' => true,
                'cart_count' => 0,
                'order_id' => $order->id,
            ])
            ->assertJsonStructure(['redirect_url', 'tracking_url'])
            ->assertSessionMissing('carrito');
    }

    public function test_poll_returns_pending_while_processing(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-POLL-002',
            'amount' => '10000.00',
            'amount_in_cents' => 1000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Processing,
            'metadata' => CheckoutSessionData::fromMetadata([
                'cart_snapshot' => [],
                'shipping' => $customer->snapshotShippingFromProfile(),
                'lines' => [],
                'subtotal' => 0,
                'shipping_fee' => 0,
                'discount' => 0,
                'total' => 0,
            ])->toMetadata(),
        ]);

        $this->actingAs($customer)
            ->getJson(route('payments.status', $payment->uuid))
            ->assertOk()
            ->assertJson([
                'status' => 'processing',
                'terminal' => false,
            ]);
    }

    public function test_return_route_clears_cart_when_payment_already_approved(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-RETURN-001',
            'amount' => '10000.00',
            'amount_in_cents' => 1000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Approved,
            'metadata' => CheckoutSessionData::fromMetadata([
                'cart_snapshot' => [],
                'shipping' => $customer->snapshotShippingFromProfile(),
                'lines' => [],
                'subtotal' => 0,
                'shipping_fee' => 0,
                'discount' => 0,
                'total' => 0,
            ])->toMetadata(),
        ]);

        $this->actingAs($customer)
            ->withSession(['carrito' => ['product:1:lb' => ['cantidad' => 1]]])
            ->get(route('payments.return', $payment->uuid))
            ->assertRedirect(route('payments.success', $payment->uuid))
            ->assertSessionMissing('carrito');
    }
}
