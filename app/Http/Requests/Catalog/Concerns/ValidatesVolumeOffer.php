<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog\Concerns;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Store\VolumeScaleService;
use App\Support\VolumeOfferConstraints;
use Closure;
use Illuminate\Validation\Rule;

trait ValidatesVolumeOffer
{
    /**
     * @return array<string, mixed>
     */
    protected function volumeOfferRules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                Rule::unique('offers', 'product_id')
                    ->where(static fn ($query) => $query
                        ->where('type', OfferType::Volume->value)
                        ->where('is_active', true))
                    ->ignore($this->route('offer')?->id),
            ],
            'volume_min_quantity' => ['required', 'numeric', $this->volumeMinimumQuantityRule()],
            'volume_sale_unit' => ['required', 'in:kg,lb'],
            'volume_offer_unit_price' => [
                'required',
                'numeric',
                'min:0',
                $this->volumeScalePriceRule(),
            ],
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

    protected function volumeScalePriceRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $productId = (int) $this->input('product_id');
            $product = Product::query()->find($productId);

            if ($product === null) {
                return;
            }

            $unit = StockUnit::resolve($this->input('volume_sale_unit'));
            $message = app(VolumeScaleService::class)->validateScaleUnitPrice(
                $product,
                $unit,
                (float) $value,
            );

            if ($message !== null) {
                $fail($message);
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
