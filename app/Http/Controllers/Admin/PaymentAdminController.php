<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\PaymentWebhook;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PaymentAdminController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $status = $request->query('status');
        $search = $request->query('search');

        $payments = Payment::query()
            ->with(['user:id,first_name,last_name,email', 'order:id,status'])
            ->when(is_string($status) && $status !== '', fn ($q) => $q->where('status', $status))
            ->when(is_string($search) && $search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', '%'.$search.'%')
                        ->orWhere('transaction_id', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $metrics = [
            'approved_today' => Payment::query()->where('status', 'approved')->whereDate('paid_at', today())->count(),
            'pending' => Payment::query()->whereIn('status', ['pending_payment', 'processing'])->count(),
            'failed_today' => Payment::query()->whereIn('status', ['declined', 'failed'])->whereDate('failed_at', today())->count(),
            'volume_today' => (float) Payment::query()->where('status', 'approved')->whereDate('paid_at', today())->sum('amount'),
        ];

        return view('admin.payments.index', compact('payments', 'metrics', 'status', 'search'));
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['user', 'order', 'attempts']);

        $webhooks = PaymentWebhook::query()
            ->where('payload->data->transaction->reference', $payment->reference)
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.payments.show', compact('payment', 'webhooks'));
    }
}
