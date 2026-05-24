<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\MarkOrderTicketPrintedRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrderTicketController extends Controller
{
    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['user', 'courier', 'items.product', 'items.offer']);

        return view('admin.pedidos.ticket', [
            'order' => $order,
        ]);
    }

    public function markPrinted(MarkOrderTicketPrintedRequest $request, Order $order): RedirectResponse
    {
        return redirect()
            ->route('admin.pedidos.ticket.show', $order)
            ->with('status', 'Ticket marcado como impreso.');
    }
}
