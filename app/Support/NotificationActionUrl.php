<?php

declare(strict_types=1);

namespace App\Support;

/**
 * URLs de notificación relativas al host actual (evita enlaces rotos al cambiar ngrok/APP_URL).
 */
final class NotificationActionUrl
{
    /**
     * @param  mixed  $parameters
     */
    public static function route(string $name, mixed $parameters = []): string
    {
        return route($name, $parameters, absolute: false);
    }

    public static function normalize(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return $url;
        }

        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        $relative = $path;
        if (is_string($query) && $query !== '') {
            $relative .= '?'.$query;
        }
        if (is_string($fragment) && $fragment !== '') {
            $relative .= '#'.$fragment;
        }

        return $relative;
    }
}
