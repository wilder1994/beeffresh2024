<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        return match ($user->role) {
            UserRole::Admin => view('dashboard'),
            UserRole::Customer => view('dashboards.customer'),
            UserRole::Cashier => view('dashboards.cashier'),
            UserRole::OrderClerk => view('dashboards.order-clerk'),
            UserRole::Delivery => view('dashboards.delivery'),
        };
    }
}
