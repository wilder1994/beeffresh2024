<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryContract
{
    public function paginateFiltered(?string $audience, ?string $search, int $perPage = 15): LengthAwarePaginator;
}
