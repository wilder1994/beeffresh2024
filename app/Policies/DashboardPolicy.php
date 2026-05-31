<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    public function viewExecutiveDashboard(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewDispatcherDashboard(User $user): bool
    {
        return $user->isDispatcher() && $user->canAccessOrderOperations();
    }
}
