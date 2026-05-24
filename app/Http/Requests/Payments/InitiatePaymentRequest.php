<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'gateway' => ['nullable', 'string', 'in:wompi,paypal,mercadopago,stripe,epayco'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function gateway(): ?string
    {
        $gateway = $this->validated('gateway');

        return is_string($gateway) && $gateway !== '' ? $gateway : null;
    }
}
