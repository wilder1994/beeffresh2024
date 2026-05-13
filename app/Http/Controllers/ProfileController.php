<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CustomerProfile;
use App\Models\SupplierProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['customerProfile', 'supplierProfile']);

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();
        unset($data['avatar']);

        $user = $request->user();
        $user->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'document_number' => $data['document_number'] ?? null,
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->isCustomer()) {
            CustomerProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'address' => $data['customer_address'],
                    'neighborhood' => $data['customer_neighborhood'] ?? null,
                    'city' => $data['customer_city'],
                    'state' => $data['customer_state'],
                    'postal_code' => $data['customer_postal_code'] ?? null,
                    'country' => $data['customer_country'] ?? 'DO',
                    'delivery_notes' => $data['customer_delivery_notes'] ?? null,
                ]
            );
        }

        if ($user->isSupplier()) {
            SupplierProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $data['supplier_company_name'] ?? null,
                    'nit' => $data['supplier_nit'],
                    'contact_name' => $data['supplier_contact_name'] ?? null,
                    'business_phone' => $data['supplier_business_phone'] ?? null,
                    'business_email' => $data['supplier_business_email'] ?? null,
                    'business_address' => $data['supplier_business_address'] ?? null,
                    'city' => $data['supplier_city'] ?? null,
                ]
            );
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
