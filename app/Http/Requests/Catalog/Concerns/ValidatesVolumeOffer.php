<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog\Concerns;

use App\Support\VolumeOfferConstraints;
use Closure;

trait ValidatesVolumeOffer
{
    /**
     * @return array<string, mixed>
     */
    protected function volumeOfferRules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'volume_min_quantity' => ['required', 'numeric', $this->volumeMinimumQuantityRule()],
            'volume_sale_unit' => ['required', 'in:kg,lb'],
            'volume_offer_unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function volumeMinimumQuantityRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $unit = (string) $this->input('volume_sale_unit', 'lb');
            $quantity = (float) $value;

            if (! VolumeOfferConstraints::meetsMinimum($quantity, $unit)) {
                $fail('La escala exige mínimo 3 lb (3 lb o 1,5 kg).');
            }
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeVolumeOfferPrices(array $data): array
    {
        $unit = (string) ($data['volume_sale_unit'] ?? 'lb');
        $price = (float) ($data['volume_offer_unit_price'] ?? 0);

        return array_merge($data, VolumeOfferConstraints::splitUnitPrice($unit, $price));
    }
}
