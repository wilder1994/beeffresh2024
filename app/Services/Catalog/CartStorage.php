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

        if (Cache::has($key)) {
            $cached = Cache::get($key);

            if (is_array($cached)) {
                if ($cached === []) {
                    session()->forget('carrito');
                } else {
                    session()->put('carrito', $cached);
                }

                return $cached;
            }
        }

        return session()->get('carrito', []);
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
        $user = auth()->user();

        if ($user instanceof User) {
            $this->forgetForUser($user->id);
        } else {
            session()->forget('carrito');
        }
    }

    public function forgetForUser(int $userId): void
    {
        Cache::put($this->cacheKey($userId), [], now()->addMinutes(self::TTL_MINUTES));

        if (auth()->id() === $userId) {
            session()->forget('carrito');
        }
    }

    private function cacheKey(int $userId): string
    {
        return self::CACHE_PREFIX.$userId;
    }
}
