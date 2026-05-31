<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dispatch;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DispatcherDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DispatcherDashboardController extends Controller
{
    public function __construct(
        private readonly DispatcherDashboardService $dashboard,
    ) {}

    public function index(): View
    {
        Gate::authorize('viewDispatcherDashboard');

        return view('dispatch.dashboard.index', $this->dashboard->metrics(auth()->user()));
    }

    public function feed(): JsonResponse
    {
        Gate::authorize('viewDispatcherDashboard');

        return response()->json($this->dashboard->feed(auth()->user()));
    }
}
