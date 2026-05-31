<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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

        $localBase = PaymentDevelopmentUrls::localBase();
        $target = $localBase.$request->getRequestUri();

        return redirect()->away($target);
    }
}
