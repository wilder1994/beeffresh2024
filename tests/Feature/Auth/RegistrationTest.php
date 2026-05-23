<?php

namespace Tests\Feature\Auth;

use App\Domain\Users\RoleSlug;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'first_name' => 'María',
            'last_name' => 'García',
            'email' => 'maria@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '3001234567',
            'document_type' => 'CC',
            'document_number' => '1234567890',
            'customer_address' => 'Calle 10 # 20-30',
            'customer_neighborhood' => 'Centro',
            'customer_city' => 'Medellín',
            'customer_state' => 'Antioquia',
            'customer_postal_code' => '050001',
            'customer_country' => 'CO',
            'customer_delivery_notes' => 'Portón negro',
            'accepts_promotions' => '1',
        ];
    }

    public function test_registration_screen_redirects_to_home_confirm(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('home', ['registro' => 'confirm']));
    }

    public function test_invalid_registration_redirects_back_with_errors(): void
    {
        $response = $this->from(route('home'))
            ->post('/register', [
                'first_name' => '',
                'last_name' => '',
                'email' => 'invalid',
                'password' => 'short',
                'password_confirmation' => 'other',
            ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password', 'phone', 'document_type', 'document_number', 'customer_address', 'customer_neighborhood', 'customer_city', 'customer_state']);
    }

    public function test_new_users_can_register_with_full_customer_profile(): void
    {
        $response = $this->post('/register', $this->validPayload());

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));

        $created = User::query()->where('email', 'maria@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole(RoleSlug::CUSTOMER));
        $this->assertSame('3001234567', $created->phone);
        $this->assertSame('CC', $created->document_type);
        $this->assertTrue($created->hasCompleteDeliveryProfile());

        $profile = $created->customerProfile;
        $this->assertNotNull($profile);
        $this->assertSame('Calle 10 # 20-30', $profile->address);
        $this->assertSame('Medellín', $profile->city);
        $this->assertSame('Antioquia', $profile->state);
        $this->assertSame('CO', $profile->country);
    }
}
