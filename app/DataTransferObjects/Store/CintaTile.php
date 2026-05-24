<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Store;

final readonly class CintaTile
{
    public function __construct(
        public string $url,
        public string $imageUrl,
        public string $title,
        public string $badge,
        public ?string $priceLabel = null,
        public ?string $availabilityLabel = null,
    ) {}
}
