<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

final class PostLoginRedirect
{
    /**
     * Ruta relativa tras autenticación (compatible con redirect()->intended()).
     */
    public static function path(User $user): string
    {
        return route(self::routeName($user), [], false);
    }

    /**
     * URL absoluta tras autenticación.
     *
     * @param  array<string, scalar|null>  $query
     */
    public static function url(User $user, array $query = []): string
    {
        $url = route(self::routeName($user));

        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $url;
    }

    private static function routeName(User $user): string
    {
        if ($user->isSupplier()) {
            return 'supplier.home';
        }

        if ($user->isStaff()) {
            return 'dashboard';
        }

        return 'home';
    }
}
