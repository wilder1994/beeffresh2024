<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Users\RoleSlug;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCourier
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User
            || ! $user->hasRole(RoleSlug::EMPLOYEE)
            || ! $user->isCourier()) {
            abort(403, 'Acceso restringido a domiciliarios.');
        }

        return $next($request);
    }
}
