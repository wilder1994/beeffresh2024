<?php

namespace Database\Factories;

use App\Domain\Users\RoleSlug;
use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if ($user->roles()->exists()) {
                return;
            }

            $user->assignRole(RoleSlug::CUSTOMER);
            CustomerProfile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'address' => fake()->streetAddress(),
                    'neighborhood' => 'Centro',
                    'city' => fake()->city(),
                    'state' => 'Antioquia',
                    'country' => 'CO',
                    'accepts_promotions' => true,
                    'loyalty_points' => 0,
                    'balance' => 0,
                ]
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'document_type' => 'CC',
            'document_number' => null,
            'phone' => fake()->numerify('8#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'avatar' => null,
            'status' => 'active',
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->syncRoles([RoleSlug::ADMIN]);
        });
    }

    public function employee(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->syncRoles([RoleSlug::EMPLOYEE]);
        });
    }

    public function supplier(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->syncRoles([RoleSlug::SUPPLIER]);
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
