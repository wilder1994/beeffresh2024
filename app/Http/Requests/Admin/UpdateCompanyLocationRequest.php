<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'company_store_address' => ['required', 'string', 'max:255'],
            'company_store_neighborhood' => ['nullable', 'string', 'max:120'],
            'company_store_city' => ['required', 'string', 'max:120'],
            'company_store_state' => ['required', 'string', 'max:120'],
            'company_store_latitude' => ['required', 'numeric', 'between:-90,90'],
            'company_store_longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function locationAttributes(): array
    {
        return [
            'store_address' => $this->input('company_store_address'),
            'store_neighborhood' => $this->input('company_store_neighborhood'),
            'store_city' => $this->input('company_store_city'),
            'store_state' => $this->input('company_store_state'),
            'store_latitude' => $this->input('company_store_latitude'),
            'store_longitude' => $this->input('company_store_longitude'),
        ];
    }
}
