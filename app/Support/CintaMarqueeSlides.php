<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Collection;

final class CintaMarqueeSlides
{
    /**
     * Repite las diapositivas hasta llenar la cinta (sin huecos con pocas imágenes).
     *
     * @template T
     * @param  Collection<int, T>  $slides
     * @return Collection<int, T>
     */
    public static function expandSegment(Collection $slides): Collection
    {
        $count = $slides->count();
        if ($count === 0) {
            return collect();
        }

        $minTiles = max($count, (int) config('cinta.marquee_min_tiles', 15));
        $expanded = collect();

        while ($expanded->count() < $minTiles) {
            foreach ($slides as $slide) {
                $expanded->push($slide);
                if ($expanded->count() >= $minTiles) {
                    break 2;
                }
            }
        }

        return $expanded;
    }

    /**
     * Dos copias del segmento para el bucle infinito (animación al -50%).
     *
     * @template T
     * @param  Collection<int, T>  $slides
     * @return Collection<int, T>
     */
    public static function track(Collection $slides): Collection
    {
        $segment = self::expandSegment($slides);

        return $segment->concat($segment->values()->all());
    }

    public static function animationDurationSeconds(Collection $slides): int
    {
        $segmentCount = self::expandSegment($slides)->count();
        $secondsPerTile = (int) config('cinta.marquee_seconds_per_tile', 2);
        $minDuration = (int) config('cinta.marquee_min_duration', 24);

        return max($minDuration, $segmentCount * $secondsPerTile);
    }
}
