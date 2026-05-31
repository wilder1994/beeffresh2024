<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Payments\PaymentDevelopmentUrls;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * En local, al volver de ngrok a localhost vía enlace del cliente, inicia sesión con token de un solo uso.
 */
final class AuthenticateDevelopmentLocalHandoff
{
    private const QUERY_PARAM = 'bf_local_handoff';

    private const CACHE_PREFIX = 'payment_local_handoff.';

    public function handle(Request $request, Closure $next): Response
    {
        if (! PaymentDevelopmentUrls::isEnabled() || ! PaymentDevelopmentUrls::isLocalRequest($request)) {
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
