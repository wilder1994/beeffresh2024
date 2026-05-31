<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Payment;
use App\Support\Payments\PaymentDevelopmentUrls;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tras el pago, el retorno firmado a localhost inicia sesión del dueño del pago.
 */
final class AuthenticatePaymentHandoff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! PaymentDevelopmentUrls::isEnabled() || auth()->check()) {
            return $next($request);
        }

        if (! $request->hasValidSignature()) {
            return $next($request);
        }

        $payment = $request->route('payment');

        if ($payment instanceof Payment) {
            auth()->login($payment->user);
        }

        return $next($request);
    }
}
