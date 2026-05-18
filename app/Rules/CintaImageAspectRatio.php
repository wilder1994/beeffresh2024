<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

final class CintaImageAspectRatio implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('Selecciona un archivo de imagen válido.');

            return;
        }

        $path = $value->getPathname();
        $size = @getimagesize($path);

        if ($size === false) {
            $fail('No se pudo leer la imagen.');

            return;
        }

        [$width, $height] = $size;

        $minW = (int) config('cinta.min_width', 960);
        $minH = (int) config('cinta.min_height', 540);

        if ($width < $minW || $height < $minH) {
            $fail('La imagen debe medir al menos '.$minW.'×'.$minH.' px.');

            return;
        }

        $targetRatio = $this->targetRatio();
        $actualRatio = $width / $height;
        $tolerance = (float) config('cinta.aspect_ratio_tolerance', 0.03);

        if ($targetRatio <= 0 || abs($actualRatio - $targetRatio) / $targetRatio > $tolerance) {
            $label = (string) config('cinta.aspect_ratio_label', '16:9');
            $percent = (int) round($tolerance * 100);

            $fail("La imagen debe ser formato {$label} (aprox.), mínimo {$minW}×{$minH} px. Margen permitido: ±{$percent}%.");
        }
    }

    private function targetRatio(): float
    {
        $ratio = (string) config('cinta.aspect_ratio', '16/9');

        if (str_contains($ratio, '/')) {
            [$w, $h] = array_map('floatval', explode('/', $ratio, 2));

            if ($w > 0 && $h > 0) {
                return $w / $h;
            }
        }

        return 16 / 9;
    }
}
