<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Payments\PaymentDevelopmentUrls;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * En local, el flujo de pago (checkout + /pago) debe correr en APP_URL (túnel HTTPS).
 * Wompi rechaza redirect-url con http://localhost (403 en el widget).
 */
final class RedirectLocalPaymentFlowToAppUrl
{
    private const LOCAL_HOSTS = ['localhost', '127.0.0.1'];

    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.env') !== 'local') {
            return $next($request);
        }

        if (PaymentDevelopmentUrls::isEnabled() && PaymentDevelopmentUrls::isPostPaymentResultPath($request)) {
            return $next($request);
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl === '' || ! str_starts_with($appUrl, 'https://')) {
            return $next($request);
        }

        $appHost = parse_url($appUrl, PHP_URL_HOST);
        if (! is_string($appHost) || $appHost === '') {
            return $next($request);
        }

        if ($request->getHost() === $appHost) {
            return $next($request);
        }

        if (! $this->isLocalBrowsingHost($request)) {
            return $next($request);
        }

        $target = $appUrl.$request->getRequestUri();
        $user = $request->user();

        if ($user !== null && PaymentDevelopmentUrls::isEnabled()) {
            $token = Str::random(40);
            Cache::put(
                'payment_tunnel_handoff.'.hash('sha256', $token),
                $user->id,
                now()->addMinutes(10),
            );
            $target .= (str_contains($target, '?') ? '&' : '?').'bf_tunnel_handoff='.$token;
        }

        return redirect()->away($target);
    }

    private function isLocalBrowsingHost(Request $request): bool
    {
        $localHost = PaymentDevelopmentUrls::localHost();

        if ($localHost !== null && $request->getHost() === $localHost) {
            return true;
        }

        return in_array($request->getHost(), self::LOCAL_HOSTS, true);
    }
}
