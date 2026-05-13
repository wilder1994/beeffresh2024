<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        $rules = [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => [
                Rule::requiredIf(fn (): bool => $user->isCustomer()),
                'nullable',
                'string',
                'max:32',
            ],
            'document_number' => ['nullable', 'string', 'max:64'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];

        if ($user->isCustomer()) {
            $rules['customer_address'] = ['required', 'string', 'max:255'];
            $rules['customer_neighborhood'] = ['nullable', 'string', 'max:120'];
            $rules['customer_city'] = ['required', 'string', 'max:120'];
            $rules['customer_state'] = ['required', 'string', 'max:120'];
            $rules['customer_postal_code'] = ['nullable', 'string', 'max:32'];
            $rules['customer_country'] = ['nullable', 'string', 'size:2'];
            $rules['customer_delivery_notes'] = ['nullable', 'string', 'max:2000'];
        }

        if ($user->isSupplier()) {
            $rules['supplier_company_name'] = ['nullable', 'string', 'max:191'];
            $rules['supplier_nit'] = ['required', 'string', 'max:64'];
            $rules['supplier_contact_name'] = ['nullable', 'string', 'max:191'];
            $rules['supplier_business_phone'] = ['nullable', 'string', 'max:32'];
            $rules['supplier_business_email'] = ['nullable', 'email', 'max:191'];
            $rules['supplier_business_address'] = ['nullable', 'string', 'max:255'];
            $rules['supplier_city'] = ['nullable', 'string', 'max:120'];
        }

        return $rules;
    }
}
