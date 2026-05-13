<?php

declare(strict_types=1);

namespace App\Domain\Users;

/** Nombres de rol Spatie (guard web). */
final class RoleSlug
{
    public const ADMIN = 'admin';

    public const EMPLOYEE = 'employee';

    public const CUSTOMER = 'customer';

    public const SUPPLIER = 'supplier';

    /** @return list<string> */
    public static function all(): array
    {
        return [self::ADMIN, self::EMPLOYEE, self::CUSTOMER, self::SUPPLIER];
    }

    public static function label(string $slug): string
    {
        return match ($slug) {
            self::ADMIN => 'Administrador',
            self::EMPLOYEE => 'Empleado',
            self::CUSTOMER => 'Cliente',
            self::SUPPLIER => 'Proveedor',
            default => $slug,
        };
    }

    public static function audienceId(string $slug): string
    {
        return match ($slug) {
            self::CUSTOMER => 'clients',
            self::SUPPLIER => 'suppliers',
            self::ADMIN, self::EMPLOYEE => 'company',
            default => 'company',
        };
    }

    public static function audienceLabel(string $slug): string
    {
        return match ($slug) {
            self::CUSTOMER => 'Clientes',
            self::SUPPLIER => 'Proveedores',
            default => 'Empresa',
        };
    }
}
