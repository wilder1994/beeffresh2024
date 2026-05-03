<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        foreach (['social_facebook', 'social_instagram', 'social_twitter', 'social_youtube'] as $key) {
            $v = $this->input($key);
            if (is_string($v) && trim($v) === '') {
                $this->merge([$key => null]);
            }
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'about_heading' => ['required', 'string', 'max:160'],
            'about_content' => ['required', 'string', 'max:20000'],
            'promise_heading' => ['required', 'string', 'max:160'],
            'promise_content' => ['required', 'string', 'max:20000'],
            'social_heading' => ['required', 'string', 'max:160'],
            'social_facebook' => ['nullable', 'string', 'max:500', 'url'],
            'social_instagram' => ['nullable', 'string', 'max:500', 'url'],
            'social_twitter' => ['nullable', 'string', 'max:500', 'url'],
            'social_youtube' => ['nullable', 'string', 'max:500', 'url'],
        ];
    }
}
