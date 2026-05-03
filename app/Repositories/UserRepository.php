<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\UserRepositoryContract;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class UserRepository implements UserRepositoryContract
{
    public function paginateFiltered(?string $audience, ?string $search, int $perPage = 15): LengthAwarePaginator
    {
        $q = User::query()->orderBy('name');

        if ($search !== null && $search !== '') {
            $term = '%'.$search.'%';
            $q->where(function ($w) use ($term): void {
                $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('document_number', 'like', $term);
            });
        }

        if ($audience === 'clients') {
            $q->where('role', UserRole::Customer);
        } elseif ($audience === 'suppliers') {
            $q->where('role', UserRole::Supplier);
        } elseif ($audience === 'company') {
            $q->whereIn('role', [
                UserRole::Admin,
                UserRole::Cashier,
                UserRole::OrderClerk,
                UserRole::Delivery,
            ]);
        }

        return $q->paginate($perPage)->withQueryString();
    }
}
