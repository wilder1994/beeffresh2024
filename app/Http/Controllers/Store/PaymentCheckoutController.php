<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\InitiatePaymentRequest;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentCheckoutController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
    ) {}

    public function process(Payment $payment): View|RedirectResponse
    {
        $this->payments->guardPaymentAccessible($payment);

        if ($payment->status === PaymentStatus::Approved && $payment->order_id) {
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

    public function return(Payment $payment, Request $request): RedirectResponse
    {
        $this->payments->guardPaymentAccessible($payment);

        $transactionId = $request->query('id');
        $transactionId = is_string($transactionId) && $transactionId !== '' ? $transactionId : null;

        $this->payments->syncFromGateway($payment, $transactionId);
        $payment->refresh();
        $this->payments->clearCartSessionIfApproved($payment);

        return match ($payment->status) {
            PaymentStatus::Approved => redirect()->route('payments.success', $payment->uuid),
            PaymentStatus::Processing, PaymentStatus::PendingPayment => redirect()->route('payments.pending', $payment->uuid),
            default => redirect()->route('payments.failed', $payment->uuid),
        };
    }

    public function status(Payment $payment, Request $request): View|JsonResponse|RedirectResponse
    {
        $this->payments->guardPaymentAccessible($payment);

        if ($request->expectsJson()) {
            return $this->poll($payment, $request);
        }

        $payment = $this->payments->refreshForPoll(
            $payment,
            is_string($request->query('id')) ? $request->query('id') : null,
        );
        $this->payments->clearCartSessionIfApproved($payment);

        if ($payment->status === PaymentStatus::Approved) {
            return redirect()->route('payments.success', $payment->uuid);
        }

        if (in_array($payment->status, [PaymentStatus::Declined, PaymentStatus::Failed, PaymentStatus::Expired], true)) {
            return redirect()->route('payments.failed', $payment->uuid);
        }

        return view('payments.status', ['payment' => $payment->load('order')]);
    }

    public function poll(Payment $payment, Request $request): JsonResponse
    {
        $this->payments->guardPaymentAccessible($payment);

        $transactionId = $request->query('transaction_id');
        $transactionId = is_string($transactionId) && $transactionId !== '' ? $transactionId : null;

        $payment = $this->payments->refreshForPoll($payment, $transactionId);

        return response()->json($this->payments->pollPayload($payment));
    }

    public function success(Payment $payment): View|RedirectResponse
    {
        $this->payments->guardPaymentAccessible($payment);
        $this->payments->clearCartSessionIfApproved($payment);

        if ($payment->status !== PaymentStatus::Approved) {
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
