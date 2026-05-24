<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\InitiatePaymentRequest;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentCheckoutController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
    ) {}

    public function process(Payment $payment): View|RedirectResponse
    {
        $this->payments->guardPaymentAccessible($payment);

        if ($payment->status->value === 'approved' && $payment->order_id) {
            return redirect()->route('payments.success', $payment->uuid);
        }

        try {
            $widget = $this->payments->checkoutWidget($payment);
        } catch (\Throwable $e) {
            return redirect()
                ->route('checkout.show')
                ->with('error', $e->getMessage());
        }

        return view('payments.process', [
            'payment' => $payment,
            'widget' => $widget,
        ]);
    }

    public function initiate(InitiatePaymentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $cart = session()->get('carrito', []);

        try {
            $payment = $this->payments->initiate(
                $user,
                $cart,
                $request->gateway(),
                $request->validated('notes'),
                $request,
            );
        } catch (\Throwable $e) {
            return redirect()
                ->route('checkout.show')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('payments.process', $payment->uuid);
    }

    public function return(Payment $payment): RedirectResponse
    {
        $this->payments->guardPaymentAccessible($payment);

        return match ($payment->status) {
            \App\Enums\PaymentStatus::Approved => redirect()->route('payments.success', $payment->uuid),
            \App\Enums\PaymentStatus::Processing, \App\Enums\PaymentStatus::PendingPayment => redirect()->route('payments.pending', $payment->uuid),
            default => redirect()->route('payments.failed', $payment->uuid),
        };
    }

    public function status(Payment $payment): View
    {
        $this->payments->guardPaymentAccessible($payment);

        return view('payments.status', ['payment' => $payment->load('order')]);
    }

    public function success(Payment $payment): View|RedirectResponse
    {
        $this->payments->guardPaymentAccessible($payment);

        if ($payment->status !== \App\Enums\PaymentStatus::Approved) {
            return redirect()->route('payments.status', $payment->uuid);
        }

        return view('payments.success', ['payment' => $payment->load('order')]);
    }

    public function pending(Payment $payment): View
    {
        $this->payments->guardPaymentAccessible($payment);

        return view('payments.pending', ['payment' => $payment]);
    }

    public function failed(Payment $payment): View
    {
        $this->payments->guardPaymentAccessible($payment);

        return view('payments.failed', ['payment' => $payment]);
    }
}
