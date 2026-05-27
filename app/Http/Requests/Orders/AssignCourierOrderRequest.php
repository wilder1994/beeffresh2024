<?php

declare(strict_types=1);

namespace App\Http\Requests\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignCourierOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order && $this->user()?->can('assign', $order);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->route('order');

        return [
            'courier_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Order $order */
            $order = $this->route('order');

            if ($order->status !== OrderStatus::ReadyForDelivery) {
                $validator->errors()->add('courier_id', 'Solo se puede asignar domiciliario a pedidos listos para entrega.');
            }

            if ($order->courier_id !== null) {
                $validator->errors()->add('courier_id', 'Este pedido ya tiene domiciliario asignado.');
            }
        });
    }
}
