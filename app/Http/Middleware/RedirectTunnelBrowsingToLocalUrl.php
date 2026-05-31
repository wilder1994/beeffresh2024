<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Payment;
use App\Support\Payments\PaymentDevelopmentUrls;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En local, el túnel (APP_URL) solo sirve el flujo de pago; el resto vuelve a APP_LOCAL_URL.
 */
final class RedirectTunnelBrowsingToLocalUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! PaymentDevelopmentUrls::isEnabled() || ! PaymentDevelopmentUrls::isTunnelRequest($request)) {
            return $next($request);
        }

        if (PaymentDevelopmentUrls::isPaymentTunnelPath($request)) {
            return $next($request);
        }

        if (PaymentDevelopmentUrls::isPostPaymentResultPath($request) && ! $request->expectsJson()) {
            $payment = $request->route('payment');
            $routeName = PaymentDevelopmentUrls::routeNameForPostPaymentPath($request->path());

            if ($payment instanceof Payment && $routeName !== null) {
                return redirect()->away(PaymentDevelopmentUrls::signedLocalUrl($routeName, $payment));
            }
        }

        $localBase = PaymentDevelopmentUrls::localBase();
        $target = $localBase.$request->getRequestUri();

        return redirect()->away($target);
    }
}
