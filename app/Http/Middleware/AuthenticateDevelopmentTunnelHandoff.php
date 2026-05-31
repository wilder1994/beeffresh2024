<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Payments\PaymentDevelopmentUrls;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Al saltar de localhost al túnel de pago, inicia sesión en ngrok con un token de un solo uso.
 */
final class AuthenticateDevelopmentTunnelHandoff
{
    private const QUERY_PARAM = 'bf_tunnel_handoff';

    private const CACHE_PREFIX = 'payment_tunnel_handoff.';

    public function handle(Request $request, Closure $next): Response
    {
        if (! PaymentDevelopmentUrls::isEnabled() || ! PaymentDevelopmentUrls::isTunnelRequest($request)) {
            return $next($request);
        }

        $token = $request->query(self::QUERY_PARAM);

        if (! is_string($token) || $token === '') {
            return $next($request);
        }

        $userId = Cache::pull(self::CACHE_PREFIX.hash('sha256', $token));

        if (is_int($userId) || (is_string($userId) && $userId !== '')) {
            auth()->loginUsingId((int) $userId);
        }

        return redirect()->to($request->url());
    }
}
