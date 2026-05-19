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
            'phone' => '8095551234',
            'document_number' => '001-1234567-8',
            'customer_address' => 'Calle Principal 123',
            'customer_neighborhood' => 'Centro',
            'customer_city' => 'Santo Domingo',
            'customer_state' => 'Distrito Nacional',
            'customer_postal_code' => '10101',
            'customer_country' => 'DO',
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
        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password', 'phone', 'customer_address', 'customer_city', 'customer_state']);
    }

    public function test_new_users_can_register_with_full_customer_profile(): void
    {
        $response = $this->post('/register', $this->validPayload());

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));

        $created = User::query()->where('email', 'maria@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole(RoleSlug::CUSTOMER));
        $this->assertSame('8095551234', $created->phone);
        $this->assertTrue($created->hasCompleteDeliveryProfile());

        $profile = $created->customerProfile;
        $this->assertNotNull($profile);
        $this->assertSame('Calle Principal 123', $profile->address);
        $this->assertSame('Santo Domingo', $profile->city);
        $this->assertSame('Distrito Nacional', $profile->state);
    }
}
