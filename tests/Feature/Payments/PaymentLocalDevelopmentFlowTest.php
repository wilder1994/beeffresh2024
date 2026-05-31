<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Support\Payments\PaymentDevelopmentUrls;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PaymentLocalDevelopmentFlowTest extends TestCase
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
        URL::forceRootUrl(config('app.url'));
    }

    public function test_tunnel_home_redirects_to_local_url(): void
    {
        $this->get('https://tunnel.example.ngrok-free.app/')
            ->assertRedirect('http://localhost:8080/');
    }

    public function test_checkout_on_localhost_redirects_to_tunnel_with_handoff_when_authenticated(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();

        $response = $this->actingAs($customer)
            ->get('http://localhost:8080/checkout');

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('https://tunnel.example.ngrok-free.app/checkout', $location);
        $this->assertStringContainsString('bf_tunnel_handoff=', $location);
    }

    public function test_tunnel_handoff_logs_user_in_on_checkout(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $token = 'handoff-token-test';
        Cache::put('payment_tunnel_handoff.'.hash('sha256', $token), $customer->id, now()->addMinutes(10));

        $this->get('https://tunnel.example.ngrok-free.app/checkout?bf_tunnel_handoff='.$token)
            ->assertRedirect('https://tunnel.example.ngrok-free.app/checkout');

        $this->assertAuthenticatedAs($customer);
    }

    public function test_payment_success_on_tunnel_stays_on_tunnel(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-LOCAL-001',
            'amount' => '10000.00',
            'amount_in_cents' => 1000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Approved,
        ]);

        $this->actingAs($customer)
            ->get('https://tunnel.example.ngrok-free.app/pago/exito/'.$payment->uuid)
            ->assertOk()
            ->assertSee('Pago aprobado');
    }

    public function test_payment_poll_json_on_tunnel_is_not_redirected_to_localhost(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-LOCAL-POLL',
            'amount' => '10000.00',
            'amount_in_cents' => 1000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Approved,
        ]);

        $this->actingAs($customer)
            ->getJson('https://tunnel.example.ngrok-free.app/pago/estado/'.$payment->uuid)
            ->assertOk()
            ->assertJsonPath('status', 'approved')
            ->assertJsonPath('redirect_url', null)
            ->assertJsonPath('catalog_url', fn ($url) => str_starts_with((string) $url, 'http://localhost:8080'));
    }

    public function test_local_handoff_logs_user_in_on_localhost(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $token = 'local-handoff-test';
        Cache::put('payment_local_handoff.'.hash('sha256', $token), $customer->id, now()->addMinutes(10));

        $this->get('http://localhost:8080/?bf_local_handoff='.$token)
            ->assertRedirect('http://localhost:8080');

        $this->assertAuthenticatedAs($customer);
    }

    public function test_signed_local_success_logs_in_and_renders(): void
    {
        $customer = User::query()->where('email', 'cliente1@demo.beeffresh.test')->firstOrFail();
        $payment = Payment::query()->create([
            'user_id' => $customer->id,
            'gateway' => PaymentGateway::Wompi,
            'reference' => 'BF-LOCAL-002',
            'amount' => '10000.00',
            'amount_in_cents' => 1000000,
            'currency' => 'COP',
            'status' => PaymentStatus::Approved,
        ]);

        $url = PaymentDevelopmentUrls::signedLocalUrl('payments.success', $payment);

        $this->followingRedirects()
            ->get($url)
            ->assertOk()
            ->assertSee('Pago aprobado');
        $this->assertAuthenticatedAs($customer);
    }
}
