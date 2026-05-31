<?php

declare(strict_types=1);

namespace App\Support\Payments;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * En local: APP_URL = túnel HTTPS (Wompi); APP_LOCAL_URL = navegación habitual (localhost:8080).
 */
final class PaymentDevelopmentUrls
{
    public static function isEnabled(): bool
    {
        if (config('app.env') !== 'local') {
            return false;
        }

        $localHost = self::localHost();
        $tunnelHost = self::tunnelHost();

        return $localHost !== null
            && $tunnelHost !== null
            && $localHost !== $tunnelHost;
    }

    public static function localBase(): string
    {
        return rtrim((string) config('app.local_url'), '/');
    }

    public static function tunnelBase(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    public static function localHost(): ?string
    {
        $host = parse_url(self::localBase(), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : null;
    }

    public static function tunnelHost(): ?string
    {
        $host = parse_url(self::tunnelBase(), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : null;
    }

    public static function isTunnelRequest(Request $request): bool
    {
        $tunnelHost = self::tunnelHost();

        return $tunnelHost !== null && $request->getHost() === $tunnelHost;
    }

    public static function isLocalRequest(Request $request): bool
    {
        $localHost = self::localHost();

        return $localHost !== null && $request->getHost() === $localHost;
    }

    public static function isPaymentTunnelPath(Request $request): bool
    {
        $path = trim($request->path(), '/');

        if ($path === 'checkout') {
            return true;
        }

        if ($path === 'checkout/pagar' && $request->isMethod('POST')) {
            return true;
        }

        if (str_starts_with($path, 'pago/procesar/')) {
            return true;
        }

        if (str_starts_with($path, 'pago/retorno/')) {
            return true;
        }

        if (str_starts_with($path, 'pago/estado/')) {
            return true;
        }

        if (str_starts_with($path, 'pago/exito/')
            || str_starts_with($path, 'pago/pendiente/')
            || str_starts_with($path, 'pago/fallido/')) {
            return true;
        }

        if (str_starts_with($path, 'webhooks/')) {
            return true;
        }

        return false;
    }

    public static function isPostPaymentResultPath(Request $request): bool
    {
        $path = trim($request->path(), '/');

        return str_starts_with($path, 'pago/exito/')
            || str_starts_with($path, 'pago/pendiente/')
            || str_starts_with($path, 'pago/fallido/')
            || str_starts_with($path, 'pago/estado/');
    }

    public static function routeNameForPostPaymentPath(string $path): ?string
    {
        $path = trim($path, '/');

        return match (true) {
            str_starts_with($path, 'pago/exito/') => 'payments.success',
            str_starts_with($path, 'pago/pendiente/') => 'payments.pending',
            str_starts_with($path, 'pago/fallido/') => 'payments.failed',
            str_starts_with($path, 'pago/estado/') => 'payments.status',
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function signedLocalUrl(string $routeName, Payment $payment, array $parameters = []): string
    {
        $parameters['payment'] = $payment->uuid;

        return self::withLocalRoot(
            fn (): string => URL::temporarySignedRoute($routeName, now()->addMinutes(30), $parameters),
        );
    }

    public static function localRoute(string $routeName, mixed $parameters = []): string
    {
        if (! self::isEnabled()) {
            return route($routeName, $parameters);
        }

        return self::withLocalRoot(fn (): string => route($routeName, $parameters));
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private static function withLocalRoot(callable $callback): mixed
    {
        $previousRoot = rtrim((string) config('app.url'), '/');
        $localScheme = parse_url(self::localBase(), PHP_URL_SCHEME) ?: 'http';

        URL::forceRootUrl(self::localBase());
        URL::forceScheme($localScheme);

        try {
            return $callback();
        } finally {
            URL::forceRootUrl($previousRoot);
            URL::forceScheme($previousRoot !== '' && str_starts_with($previousRoot, 'https') ? 'https' : null);
        }
    }

    public static function localHandoffUrl(string $routeName, mixed $parameters = []): string
    {
        $url = self::localRoute($routeName, $parameters);

        if (! self::isEnabled() || ! auth()->check()) {
            return $url;
        }

        $token = Str::random(40);
        Cache::put(
            'payment_local_handoff.'.hash('sha256', $token),
            auth()->id(),
            now()->addMinutes(10),
        );

        return $url.(str_contains($url, '?') ? '&' : '?').'bf_local_handoff='.$token;
    }

    public static function urlForPaymentRoute(string $routeName, Payment $payment): string
    {
        if (! self::isEnabled()) {
            return route($routeName, $payment);
        }

        return self::signedLocalUrl($routeName, $payment);
    }

    public static function redirectToLocalRoute(string $routeName, Payment $payment): RedirectResponse
    {
        if (! self::isEnabled() || self::isLocalRequest(request())) {
            return redirect()->route($routeName, $payment);
        }

        return redirect()->away(self::signedLocalUrl($routeName, $payment));
    }
}
