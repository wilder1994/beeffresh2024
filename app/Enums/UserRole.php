<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case Cashier = 'cashier';
    case OrderClerk = 'order_clerk';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Cliente',
            self::Admin => 'Administrador',
            self::Cashier => 'Caja',
            self::OrderClerk => 'Registro de pedidos',
            self::Delivery => 'Domiciliario',
        };
    }

    /** Roles internos de la carnicería (no cliente comprador). */
    public function isStaff(): bool
    {
        return $this !== self::Customer;
    }
}
