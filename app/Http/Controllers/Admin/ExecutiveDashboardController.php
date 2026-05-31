<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\AdminExecutiveDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ExecutiveDashboardController extends Controller
{
    public function __construct(
        private readonly AdminExecutiveDashboardService $dashboard,
    ) {}

    public function index(): View
    {
        Gate::authorize('viewExecutiveDashboard');

        return view('admin.dashboard.executive', $this->dashboard->metrics());
    }

    public function feed(): JsonResponse
    {
        Gate::authorize('viewExecutiveDashboard');

        return response()->json($this->dashboard->feed());
    }
}
