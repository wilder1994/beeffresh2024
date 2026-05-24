<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user !== null && $user->isCustomer(), 403);

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with(['courier:id,first_name,last_name'])
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $activeCount = Order::query()
            ->where('user_id', $user->id)
            ->activeForOperations()
            ->count();

        return view('store.orders.index', [
            'orders' => $orders,
            'activeCount' => $activeCount,
        ]);
    }
}
