<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Users\RoleSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Support\PostLoginRedirect;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('home', ['registro' => 'confirm']);
    }

    public function store(RegisterCustomerRequest $request): RedirectResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();

        $user = User::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'document_number' => $data['document_number'] ?? null,
            'status' => 'active',
        ]);

        $user->assignRole(RoleSlug::CUSTOMER);

        CustomerProfile::query()->create([
            'user_id' => $user->id,
            'address' => $data['customer_address'],
            'neighborhood' => $data['customer_neighborhood'] ?? null,
            'city' => $data['customer_city'],
            'state' => $data['customer_state'],
            'postal_code' => $data['customer_postal_code'] ?? null,
            'country' => $data['customer_country'] ?? 'CO',
            'delivery_notes' => $data['customer_delivery_notes'] ?? null,
            'accepts_promotions' => $request->boolean('accepts_promotions', true),
            'loyalty_points' => 0,
            'balance' => 0,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(PostLoginRedirect::url($user));
    }
}
