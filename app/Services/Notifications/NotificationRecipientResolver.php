<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Domain\Users\PermissionKey;
use App\Domain\Users\RoleSlug;
use App\Models\Order;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Collection;

final class NotificationRecipientResolver
{
    /**
     * @return Collection<int, User>
     */
    public function operationsStaff(): Collection
    {
        return User::query()
            ->where(function ($query): void {
                $query->role(RoleSlug::ADMIN)
                    ->orWhere->permission(PermissionKey::MODULE_ORDERS);
            })
            ->get()
            ->unique('id')
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    public function dispatchers(): Collection
    {
        return User::query()
            ->whereHas('employeeProfile.position', fn ($q) => $q->where('slug', Position::SLUG_DISPATCH))
            ->get();
    }

    public function customerForOrder(Order $order): ?User
    {
        return $order->user;
    }

    public function courierForOrder(Order $order): ?User
    {
        return $order->courier;
    }

    /**
     * @return Collection<int, User>
     */
    public function forAudiences(array $audiences, array $payload): Collection
    {
        $recipients = collect();

        foreach ($audiences as $audience) {
            $recipients = $recipients->merge(match ($audience) {
                'customer' => $this->customerAudience($payload),
                'courier' => $this->courierAudience($payload),
                'operations' => $this->operationsStaff(),
                default => collect(),
            });
        }

        return $recipients->filter()->unique('id')->values();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return Collection<int, User>
     */
    private function customerAudience(array $payload): Collection
    {
        $order = $payload['order'] ?? null;
        $user = $payload['user'] ?? null;

        if ($user instanceof User && $user->isCustomer()) {
            return collect([$user]);
        }

        if ($order instanceof Order && $order->user !== null) {
            return collect([$order->user]);
        }

        return collect();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return Collection<int, User>
     */
    private function courierAudience(array $payload): Collection
    {
        $order = $payload['order'] ?? null;
        $courier = $payload['courier'] ?? null;

        if ($courier instanceof User) {
            return collect([$courier]);
        }

        if ($order instanceof Order && $order->courier !== null) {
            return collect([$order->courier]);
        }

        return collect();
    }
}
