<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\UserRepositoryContract;
use App\Domain\Users\RoleSlug;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class UserRepository implements UserRepositoryContract
{
    public function paginateFiltered(?string $audience, ?string $search, int $perPage = 15): LengthAwarePaginator
    {
        $q = User::query()
            ->with(['roles', 'customerProfile', 'supplierProfile'])
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($search !== null && $search !== '') {
            $term = '%'.$search.'%';
            $q->where(function ($w) use ($term): void {
                $w->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('document_number', 'like', $term)
                    ->orWhereRaw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,'')) like ?", [$term]);
            });
        }

        if ($audience === 'clients') {
            $q->role(RoleSlug::CUSTOMER);
        } elseif ($audience === 'suppliers') {
            $q->role(RoleSlug::SUPPLIER);
        } elseif ($audience === 'company') {
            $q->where(function ($w): void {
                $w->whereHas('roles', fn ($r) => $r->where('name', RoleSlug::ADMIN))
                    ->orWhereHas('roles', fn ($r) => $r->where('name', RoleSlug::EMPLOYEE));
            });
        }

        return $q->paginate($perPage)->withQueryString();
    }
}
