<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Models\CompanyProfile;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CourierAssignmentService
{
    public function assignNearestAvailable(Order $order, ?User $assignedBy = null): OrderAssignment
    {
        if ($order->shipping_latitude === null || $order->shipping_longitude === null) {
            throw new RuntimeException('El pedido no tiene coordenadas de entrega.');
        }

        $destinationLat = (float) $order->shipping_latitude;
        $destinationLng = (float) $order->shipping_longitude;

        $courier = $this->findNearestAvailableCourier($destinationLat, $destinationLng);

        if ($courier === null) {
            throw new RuntimeException('No hay domiciliarios disponibles.');
        }

        return DB::transaction(function () use ($order, $courier, $assignedBy): OrderAssignment {
            $this->releaseActiveAssignments($order);

            $assignment = OrderAssignment::query()->create([
                'order_id' => $order->id,
                'courier_id' => $courier->id,
                'assigned_by_user_id' => $assignedBy?->id,
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            $order->courier_id = $courier->id;
            $order->assigned_at = now();
            $order->save();

            $this->markCourierBusy($courier);

            return $assignment;
        });
    }

    public function assignToCourier(Order $order, User $courier, ?User $assignedBy = null): OrderAssignment
    {
        if (! $courier->isCourier()) {
            throw new RuntimeException('El usuario seleccionado no es domiciliario.');
        }

        return DB::transaction(function () use ($order, $courier, $assignedBy): OrderAssignment {
            $this->releaseActiveAssignments($order);

            $assignment = OrderAssignment::query()->create([
                'order_id' => $order->id,
                'courier_id' => $courier->id,
                'assigned_by_user_id' => $assignedBy?->id,
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            $order->courier_id = $courier->id;
            $order->assigned_at = now();
            $order->save();

            $this->markCourierBusy($courier);

            return $assignment;
        });
    }

    public function releaseCourier(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $this->releaseActiveAssignments($order);

            if ($order->courier_id !== null) {
                $courier = User::query()->find($order->courier_id);
                if ($courier !== null) {
                    $this->markCourierFree($courier);
                }
            }

            $order->courier_id = null;
            $order->save();
        });
    }

    public function markCourierFree(User $courier): void
    {
        $profile = $courier->employeeProfile;
        if ($profile === null) {
            return;
        }

        $profile->available = true;
        $profile->save();
    }

    public function markCourierBusy(User $courier): void
    {
        $profile = $courier->employeeProfile;
        if ($profile === null) {
            return;
        }

        $profile->available = false;
        $profile->save();
    }

    public function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function findNearestAvailableCourier(float $destinationLat, float $destinationLng): ?User
    {
        /** @var Collection<int, User> $candidates */
        $candidates = User::query()
            ->whereHas('employeeProfile', function (Builder $query): void {
                $query->where('available', true)
                    ->whereHas('position', fn (Builder $position) => $position->where('slug', \App\Models\Position::SLUG_DELIVERY));
            })
            ->with(['employeeProfile', 'courierLocations' => fn ($q) => $q->limit(1)])
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        $store = CompanyProfile::singleton();
        $storeLat = $store->store_latitude !== null ? (float) $store->store_latitude : null;
        $storeLng = $store->store_longitude !== null ? (float) $store->store_longitude : null;

        $nearest = null;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($candidates as $courier) {
            $location = $courier->courierLocations->first();
            if ($location !== null) {
                $lat = (float) $location->latitude;
                $lng = (float) $location->longitude;
            } elseif ($courier->employeeProfile?->home_latitude !== null && $courier->employeeProfile?->home_longitude !== null) {
                $lat = (float) $courier->employeeProfile->home_latitude;
                $lng = (float) $courier->employeeProfile->home_longitude;
            } elseif ($storeLat !== null && $storeLng !== null) {
                $lat = $storeLat;
                $lng = $storeLng;
            } else {
                continue;
            }

            $distance = $this->haversineKm($lat, $lng, $destinationLat, $destinationLng);

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $courier;
            }
        }

        return $nearest;
    }

    private function releaseActiveAssignments(Order $order): void
    {
        OrderAssignment::query()
            ->where('order_id', $order->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'released_at' => now(),
            ]);
    }
}
