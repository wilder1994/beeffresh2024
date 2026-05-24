<?php

declare(strict_types=1);

namespace App\Enums;

enum DeliveryProofType: string
{
    case Signature = 'signature';
    case Photo = 'photo';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::Signature => 'Firma',
            self::Photo => 'Foto',
            self::Video => 'Video',
        };
    }
}
