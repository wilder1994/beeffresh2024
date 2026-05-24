<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Normaliza distintos formatos de URL de YouTube a https://www.youtube.com/embed/{id}
 */
final class YoutubeEmbedUrl
{
    public static function resolve(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('#[?&]v=([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('#youtube\.com/shorts/([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        return null;
    }

    public static function videoId(string $url): ?string
    {
        $embed = self::resolve($url);

        if ($embed === null) {
            return null;
        }

        if (preg_match('#/embed/([a-zA-Z0-9_-]{11})#', $embed, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function thumbnailUrl(string $url, string $quality = 'hqdefault'): ?string
    {
        $id = self::videoId($url);

        if ($id === null) {
            return null;
        }

        return 'https://img.youtube.com/vi/'.$id.'/'.$quality.'.jpg';
    }
}
