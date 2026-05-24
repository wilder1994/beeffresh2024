<?php

declare(strict_types=1);

namespace App\Http\Requests\Orders;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class RedispatchOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && $this->user()?->can('transition', $order) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'redelivery_fee' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function redeliveryFee(): ?float
    {
        if (! $this->filled('redelivery_fee')) {
            return null;
        }

        return (float) $this->validated('redelivery_fee');
    }
}
