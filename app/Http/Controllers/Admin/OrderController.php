<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $pedidos = Order::query()
            ->with(['user', 'items.producto'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pedidos.index', compact('pedidos'));
    }
}
