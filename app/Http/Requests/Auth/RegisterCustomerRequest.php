<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterCustomerRequest extends FormRequest
{
    /** @var list<string> */
    public const FIELD_KEYS = [
        'first_name',
        'last_name',
        'email',
        'password',
        'password_confirmation',
        'phone',
        'document_number',
        'customer_address',
        'customer_neighborhood',
        'customer_city',
        'customer_state',
        'customer_postal_code',
        'customer_country',
        'customer_delivery_notes',
        'accepts_promotions',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:32'],
            'document_number' => ['nullable', 'string', 'max:64'],
            'customer_address' => ['required', 'string', 'max:255'],
            'customer_neighborhood' => ['nullable', 'string', 'max:120'],
            'customer_city' => ['required', 'string', 'max:120'],
            'customer_state' => ['required', 'string', 'max:120'],
            'customer_postal_code' => ['nullable', 'string', 'max:32'],
            'customer_country' => ['nullable', 'string', 'size:2'],
            'customer_delivery_notes' => ['nullable', 'string', 'max:2000'],
            'accepts_promotions' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'nombre',
            'last_name' => 'apellidos',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'phone' => 'teléfono',
            'document_number' => 'identificación',
            'customer_address' => 'dirección',
            'customer_neighborhood' => 'barrio',
            'customer_city' => 'ciudad',
            'customer_state' => 'provincia',
            'customer_postal_code' => 'código postal',
            'customer_country' => 'país',
            'customer_delivery_notes' => 'indicaciones de entrega',
        ];
    }
}
