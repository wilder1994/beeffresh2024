<?php

declare(strict_types=1);

namespace App\Http\Requests\Orders;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderOperationsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Order::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tab' => ['nullable', 'string', Rule::in([
                'all',
                'pending',
                'preparing',
                'ready',
                'in_delivery',
                'delivered',
                'failed',
                'returned',
                'cancelled',
            ])],
            'search' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function tab(): string
    {
        return (string) ($this->validated('tab') ?? 'all');
    }

    public function search(): ?string
    {
        $search = $this->validated('search');

        if ($search === null || trim((string) $search) === '') {
            return null;
        }

        return trim((string) $search);
    }
}
