<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Carrito en sesión; para usuarios autenticados también en caché (mismo carrito localhost ↔ ngrok).
 */
final class CartStorage
{
    private const CACHE_PREFIX = 'cart.user.';

    private const TTL_MINUTES = 24 * 60;

    /** @return array<string|int, array<string, mixed>> */
    public function get(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return session()->get('carrito', []);
        }

        $key = $this->cacheKey($user->id);
        $cached = Cache::get($key);

        if (is_array($cached) && $cached !== []) {
            session()->put('carrito', $cached);

            return $cached;
        }

        $sessionCart = session()->get('carrito', []);

        if ($sessionCart !== []) {
            Cache::put($key, $sessionCart, now()->addMinutes(self::TTL_MINUTES));
        }

        return $sessionCart;
    }

    /** @param array<string|int, array<string, mixed>> $cart */
    public function put(array $cart): void
    {
        session()->put('carrito', $cart);

        $user = auth()->user();

        if ($user instanceof User) {
            Cache::put($this->cacheKey($user->id), $cart, now()->addMinutes(self::TTL_MINUTES));
        }
    }

    public function forget(): void
    {
        session()->forget('carrito');

        $user = auth()->user();

        if ($user instanceof User) {
            Cache::forget($this->cacheKey($user->id));
        }
    }

    private function cacheKey(int $userId): string
    {
        return self::CACHE_PREFIX.$userId;
    }
}
