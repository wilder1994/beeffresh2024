<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Users\RoleSlug;
use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Support\PostLoginRedirect;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $full = trim((string) $request->name);
        $parts = preg_split('/\s+/', $full, 2, PREG_SPLIT_NO_EMPTY) ?: [];
        $first = $parts[0] ?? 'Usuario';
        $last = $parts[1] ?? '';

        $user = User::query()->create([
            'first_name' => $first,
            'last_name' => $last,
            'email' => $request->email,
            'password' => $request->password,
            'status' => 'active',
        ]);

        $user->assignRole(RoleSlug::CUSTOMER);
        CustomerProfile::query()->create([
            'user_id' => $user->id,
            'country' => 'DO',
            'accepts_promotions' => true,
            'loyalty_points' => 0,
            'balance' => 0,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(PostLoginRedirect::url($user));
    }
}
