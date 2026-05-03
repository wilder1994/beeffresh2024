<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\AdminDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(AdminDashboardService $adminDashboard): View|RedirectResponse
    {
        $user = auth()->user();

        return match ($user->role) {
            UserRole::Admin => view('dashboard', $adminDashboard->metrics()),
            UserRole::Customer => view('dashboards.customer'),
            UserRole::Cashier => view('dashboards.cashier'),
            UserRole::OrderClerk => view('dashboards.order-clerk'),
            UserRole::Delivery => view('dashboards.delivery'),
            UserRole::Supplier => redirect()->route('supplier.home'),
        };
    }
}
