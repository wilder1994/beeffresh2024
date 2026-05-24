<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\Domain\Catalog\StockUnit;
use App\Domain\Store\OfferType;
use App\Http\Requests\Catalog\Concerns\ValidatesVolumeOffer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfferRequest extends FormRequest
{
    use ValidatesVolumeOffer;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = OfferType::tryFrom((string) $this->input('type', $this->route('offer')?->type?->value));

        $rules = [
            'type' => ['required', Rule::enum(OfferType::class)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'is_active' => ['sometimes', 'boolean'],
            'show_on_cinta' => ['sometimes', 'boolean'],
            'show_on_home' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        if ($type === OfferType::Bundle) {
            $rules['offer_price'] = ['required', 'numeric', 'min:0'];
            $rules['items'] = ['required', 'array', 'min:2'];
            $rules['items.*.product_id'] = ['required', 'integer', 'exists:products,id'];
            $rules['items.*.quantity'] = ['required', 'numeric', 'min:0.01'];
            $rules['items.*.sale_unit'] = ['required', Rule::enum(StockUnit::class)];
        }

        if ($type === OfferType::Volume) {
            $rules = array_merge($rules, $this->volumeOfferRules());
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if (! is_array($data)) {
            return $data;
        }

        if (OfferType::tryFrom((string) ($data['type'] ?? '')) === OfferType::Volume) {
            return $this->mergeVolumeOfferPrices($data);
        }

        return $data;
    }
}
