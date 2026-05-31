<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\DataTransferObjects\Payments\CheckoutSessionData;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\PaymentWebhookStatus;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use App\Models\Product;
use App\Models\User;
use App\Services\Payments\Gateways\WompiGateway;
use App\Services\Payments\PaymentWebhookProcessor;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentWebhookFlowTest extends TestCase
{
    use RefreshDatabase;

    private const EVENTS_SECRET = 'test_events_secret_key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoUsersSeeder::class);

        Config::set('payments.gateways.wompi.events_secret', self::EVENTS_SECRET);
        Config::set('payments.gateways.wompi.integrity_secret', 'test_integrity');
        Config::set('payments.gateways.wompi.public_key', 'pub_test');
    }

    public function test_approved_webhook_creates_order_and_clears_cart(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();
        $product = Product::factory()->create(['stock' => 20, 'price_per_lb' => 15000]);

        $cart = [
            "product:{$product->id}:lb" => [
                'type' => 'product',
                'product_id' => $product->id,
                'sale_unit' => 'lb',
                'cantidad' => 2,
            ],
        ];

        session(['carrito' => $cart]);

        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-TEST-001',
            'amount' => '30000.00',
            'amount_in_cents' => 3000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Processing,
            'metadata' => app(\App\Services\Payments\CheckoutQuoteService::class)
                ->build($customer, $cart)
                ->toMetadata(),
        ]);

        $payload = $this->signedWebhookPayload($payment->reference, 'APPROVED', '3000000');

        $this->postJson(route('webhooks.wompi'), $payload, [
            'X-Event-Checksum' => $payload['signature']['checksum'],
        ])->assertOk();

        $payment->refresh();
        $this->assertSame(PaymentStatus::Approved, $payment->status);
        $this->assertNotNull($payment->order_id);

        $product->refresh();
        $this->assertEquals(19.0, (float) $product->stock);

        $this->assertSame([], Cache::get('cart.user.'.$customer->id));
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        $customer = User::query()->where('email', 'cliente2@demo.beeffresh.test')->firstOrFail();

        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-TEST-DUP',
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

        $payload = $this->signedWebhookPayload($payment->reference, 'DECLINED', '1000000');

        $this->postJson(route('webhooks.wompi'), $payload, [
            'X-Event-Checksum' => $payload['signature']['checksum'],
        ]);

        $count = PaymentWebhook::query()->count();

        $this->postJson(route('webhooks.wompi'), $payload, [
            'X-Event-Checksum' => $payload['signature']['checksum'],
        ]);

        $this->assertSame($count, PaymentWebhook::query()->count());
    }

    public function test_invalid_checksum_is_ignored(): void
    {
        $payload = [
            'event' => 'transaction.updated',
            'data' => ['transaction' => ['id' => 'tx-1', 'status' => 'APPROVED', 'amount_in_cents' => 100, 'reference' => 'X']],
            'signature' => ['properties' => ['transaction.id', 'transaction.status', 'transaction.amount_in_cents'], 'checksum' => 'bad'],
            'timestamp' => 1234567890,
        ];

        $this->postJson(route('webhooks.wompi'), $payload)->assertOk();

        $webhook = PaymentWebhook::query()->first();
        $this->assertNotNull($webhook);
        $this->assertSame(PaymentWebhookStatus::Ignored, $webhook->status);
        $this->assertFalse($webhook->checksum_valid);
    }

    /**
     * @return array<string, mixed>
     */
    private function signedWebhookPayload(string $reference, string $status, string $amountInCents): array
    {
        $timestamp = 1700000000;
        $transactionId = 'tx-'.md5($reference.$status);

        $properties = ['transaction.id', 'transaction.status', 'transaction.amount_in_cents'];
        $concatenated = $transactionId.$status.$amountInCents.$timestamp.self::EVENTS_SECRET;
        $checksum = hash('sha256', $concatenated);

        return [
            'event' => 'transaction.updated',
            'data' => [
                'transaction' => [
                    'id' => $transactionId,
                    'status' => $status,
                    'amount_in_cents' => (int) $amountInCents,
                    'reference' => $reference,
                    'payment_method_type' => 'CARD',
                ],
            ],
            'signature' => [
                'properties' => $properties,
                'checksum' => $checksum,
            ],
            'timestamp' => $timestamp,
        ];
    }
}
