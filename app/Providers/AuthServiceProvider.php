<?php

namespace App\Providers;

use App\Domain\Users\RoleSlug;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            if ($user instanceof User && $user->hasRole(RoleSlug::ADMIN)) {
                return true;
            }

            return null;
        });
    }
}
