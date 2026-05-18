<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
            $user->hasRole(RoleSlug::EMPLOYEE) => view('dashboards.employee'),
            default => view('dashboards.employee'),
        };
    }
}
