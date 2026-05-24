<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Users\PermissionKey;
use App\Domain\Users\RoleSlug;
use App\Services\AdminDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(AdminDashboardService $adminDashboard): View|RedirectResponse
    {
        $user = auth()->user();

        return match (true) {
            $user->hasRole(RoleSlug::ADMIN) => view('dashboard', $adminDashboard->metrics()),
            $user->hasRole(RoleSlug::CUSTOMER) => redirect()->route('home'),
            $user->hasRole(RoleSlug::SUPPLIER) => redirect()->route('supplier.home'),
            $user->hasRole(RoleSlug::EMPLOYEE) && $user->canAccessCourierModule() => redirect()->route('courier.orders.index'),
            $user->hasRole(RoleSlug::EMPLOYEE) && $user->can(PermissionKey::MODULE_ORDERS) && $user->isDispatcher() => redirect()->route('admin.pedidos.index'),
            $user->hasRole(RoleSlug::EMPLOYEE) && $user->can(PermissionKey::MODULE_ORDERS) => redirect()->route('admin.pedidos.index'),
            $user->hasRole(RoleSlug::EMPLOYEE) => view('dashboards.employee'),
            default => view('dashboards.employee'),
        };
    }
}
