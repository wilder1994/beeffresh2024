<?php

declare(strict_types=1);

namespace App\Http\Requests\Orders;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class MarkOrderDeliveredRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        $user = $this->user();

        return $order instanceof Order
            && $user !== null
            && $user->can('transition', $order)
            && $user->can('addDeliveryProof', $order);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'signature' => ['required', 'string', 'max:500000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
