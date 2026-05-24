<?php

declare(strict_types=1);

namespace App\Http\Requests\Orders;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderTrackingFeedRequest extends FormRequest
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
            'since' => ['nullable', 'date'],
        ];
    }

    public function since(): ?string
    {
        $since = $this->validated('since');

        return $since !== null ? (string) $since : null;
    }
}
