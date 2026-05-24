<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Payments;

final readonly class CheckoutLineItem
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $type,
        public string $name,
        public float $quantity,
        public string $saleUnit,
        public float $unitPrice,
        public float $subtotal,
        public array $meta = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'sale_unit' => $this->saleUnit,
            'unit_price' => $this->unitPrice,
            'subtotal' => $this->subtotal,
            'meta' => $this->meta,
        ];
    }
}
