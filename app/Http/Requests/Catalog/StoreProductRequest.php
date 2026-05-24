<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\Domain\Catalog\ProductStatus;
use App\Domain\Catalog\SaleType;
use App\Domain\Catalog\StockUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'meat_type_id' => ['required', 'integer', 'exists:meat_types,id'],
            'meat_cut_id' => ['required', 'integer', 'exists:meat_cuts,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'price_per_lb' => ['required', 'numeric', 'min:0'],
            'promo_price_kg' => ['nullable', 'numeric', 'min:0'],
            'promo_price_lb' => ['nullable', 'numeric', 'min:0'],
            'promo_start' => ['nullable', 'date'],
            'promo_end' => ['nullable', 'date', 'after_or_equal:promo_start'],
            'stock' => ['required', 'numeric', 'min:0'],
            'stock_unit' => ['required', Rule::enum(StockUnit::class)],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'sale_type' => ['required', Rule::enum(SaleType::class)],
            'featured' => ['sometimes', 'boolean'],
            'show_on_cinta' => ['sometimes', 'boolean'],
        ];
    }
}
