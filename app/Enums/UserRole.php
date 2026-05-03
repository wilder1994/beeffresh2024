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
    case Supplier = 'supplier';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Cliente',
            self::Admin => 'Administrador',
            self::Cashier => 'Caja',
            self::OrderClerk => 'Registro de pedidos',
            self::Delivery => 'Domiciliario',
            self::Supplier => 'Proveedor',
        };
    }

    /** Personal de la empresa (gestión interna). */
    public function isStaff(): bool
    {
        return match ($this) {
            self::Admin, self::Cashier, self::OrderClerk, self::Delivery => true,
            default => false,
        };
    }

    public function isSupplier(): bool
    {
        return $this === self::Supplier;
    }
}
