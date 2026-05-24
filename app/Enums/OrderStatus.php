<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case ReadyForDelivery = 'ready_for_delivery';
    case PickedUp = 'picked_up';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case DeliveryFailed = 'delivery_failed';
    case ReturnedToStore = 'returned_to_store';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Preparing => 'En preparación',
            self::ReadyForDelivery => 'Listo para entrega',
            self::PickedUp => 'Recogido',
            self::InTransit => 'En tránsito',
            self::Delivered => 'Entregado',
            self::DeliveryFailed => 'Entrega fallida',
            self::ReturnedToStore => 'Devuelto a tienda',
            self::Cancelled => 'Cancelado',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-100 text-yellow-800',
            self::Preparing => 'bg-blue-100 text-blue-800',
            self::ReadyForDelivery => 'bg-indigo-100 text-indigo-800',
            self::PickedUp => 'bg-purple-100 text-purple-800',
            self::InTransit => 'bg-cyan-100 text-cyan-800',
            self::Delivered => 'bg-green-100 text-green-800',
            self::DeliveryFailed => 'bg-red-100 text-red-800',
            self::ReturnedToStore => 'bg-orange-100 text-orange-800',
            self::Cancelled => 'bg-gray-100 text-gray-800',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return false;
        }

        if (in_array($this, self::terminalStatuses(), true)) {
            return false;
        }

        return match ($this) {
            self::Pending => in_array($target, [self::Preparing, self::Cancelled], true),
            self::Preparing => in_array($target, [self::ReadyForDelivery, self::Cancelled], true),
            self::ReadyForDelivery => in_array($target, [self::PickedUp, self::Cancelled], true),
            self::PickedUp => in_array($target, [self::InTransit, self::DeliveryFailed, self::Cancelled], true),
            self::InTransit => in_array($target, [self::Delivered, self::DeliveryFailed], true),
            self::DeliveryFailed => in_array($target, [self::ReturnedToStore, self::InTransit, self::Cancelled], true),
            self::ReturnedToStore => in_array($target, [self::Preparing, self::Cancelled], true),
            default => false,
        };
    }

    /** @return list<self> */
    public static function activeCourierStatuses(): array
    {
        return [
            self::PickedUp,
            self::InTransit,
        ];
    }

    /** @return list<self> */
    public static function terminalStatuses(): array
    {
        return [
            self::Delivered,
            self::Cancelled,
        ];
    }
}
