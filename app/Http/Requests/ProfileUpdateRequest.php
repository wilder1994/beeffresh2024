<?php

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => [
                Rule::requiredIf(fn (): bool => $user->isCustomer()),
                'nullable',
                'string',
                'max:32',
            ],
            'document_number' => ['nullable', 'string', 'max:64'],
            'company_name' => ['nullable', 'string', 'max:191'],
            'address_line1' => [
                Rule::requiredIf(fn (): bool => $user->isCustomer()),
                'nullable',
                'string',
                'max:191',
            ],
            'address_line2' => ['nullable', 'string', 'max:191'],
            'city' => [
                Rule::requiredIf(fn (): bool => $user->isCustomer()),
                'nullable',
                'string',
                'max:120',
            ],
            'state' => [
                Rule::requiredIf(fn (): bool => $user->isCustomer()),
                'nullable',
                'string',
                'max:120',
            ],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'country' => ['nullable', 'string', 'size:2'],
            'delivery_instructions' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
