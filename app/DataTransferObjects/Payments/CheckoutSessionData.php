<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Payments;

final readonly class CheckoutSessionData
{
    /**
     * @param  list<CheckoutLineItem>  $lines
     * @param  array<string, mixed>  $cartSnapshot
     * @param  array<string, mixed>  $shipping
     */
    public function __construct(
        public array $lines,
        public array $cartSnapshot,
        public array $shipping,
        public float $subtotal,
        public float $shippingFee,
        public float $discount,
        public float $total,
        public ?string $notes = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toMetadata(): array
    {
        return [
            'cart_snapshot' => $this->cartSnapshot,
            'shipping' => $this->shipping,
            'lines' => array_map(static fn (CheckoutLineItem $line): array => $line->toArray(), $this->lines),
            'subtotal' => $this->subtotal,
            'shipping_fee' => $this->shippingFee,
            'discount' => $this->discount,
            'total' => $this->total,
            'notes' => $this->notes,
        ];
    }

    public static function fromMetadata(array $metadata): self
    {
        $lines = array_map(
            static fn (array $line): CheckoutLineItem => new CheckoutLineItem(
                type: (string) $line['type'],
                name: (string) $line['name'],
                quantity: (float) $line['quantity'],
                saleUnit: (string) $line['sale_unit'],
                unitPrice: (float) $line['unit_price'],
                subtotal: (float) $line['subtotal'],
                meta: is_array($line['meta'] ?? null) ? $line['meta'] : [],
            ),
            $metadata['lines'] ?? [],
        );

        return new self(
            lines: $lines,
            cartSnapshot: is_array($metadata['cart_snapshot'] ?? null) ? $metadata['cart_snapshot'] : [],
            shipping: is_array($metadata['shipping'] ?? null) ? $metadata['shipping'] : [],
            subtotal: (float) ($metadata['subtotal'] ?? 0),
            shippingFee: (float) ($metadata['shipping_fee'] ?? 0),
            discount: (float) ($metadata['discount'] ?? 0),
            total: (float) ($metadata['total'] ?? 0),
            notes: isset($metadata['notes']) ? (string) $metadata['notes'] : null,
        );
    }
}
