<?php

declare(strict_types=1);

namespace App\Domain\Users;

/** Permisos directos (empleados) y comprobaciones `can()`. */
final class PermissionKey
{
    public const READ_ONLY = 'access.read_only';

    public const MODULE_MANAGEMENT = 'module.management';

    public const MODULE_OPERATIONS = 'module.operations';

    public const MODULE_USERS = 'module.users';

    public const MODULE_SETTINGS = 'module.settings';

    public const MODULE_SALES = 'module.sales';

    public const MODULE_INVENTORY = 'module.inventory';

    public const MODULE_REPORTS = 'module.reports';

    public const MODULE_ORDERS = 'module.orders';

    public const MODULE_CATALOG = 'module.catalog';

    /** @return list<string> */
    public static function employeeModuleKeys(): array
    {
        return [
            self::READ_ONLY,
            self::MODULE_MANAGEMENT,
            self::MODULE_OPERATIONS,
            self::MODULE_USERS,
            self::MODULE_SETTINGS,
            self::MODULE_SALES,
            self::MODULE_INVENTORY,
            self::MODULE_REPORTS,
            self::MODULE_ORDERS,
            self::MODULE_CATALOG,
        ];
    }

    public static function label(string $key): string
    {
        return match ($key) {
            self::READ_ONLY => 'Solo lectura',
            self::MODULE_MANAGEMENT => 'Gestión',
            self::MODULE_OPERATIONS => 'Operaciones',
            self::MODULE_USERS => 'Usuarios',
            self::MODULE_SETTINGS => 'Ajustes',
            self::MODULE_SALES => 'Ventas',
            self::MODULE_INVENTORY => 'Inventario',
            self::MODULE_REPORTS => 'Reportes',
            self::MODULE_ORDERS => 'Pedidos',
            self::MODULE_CATALOG => 'Catálogo',
            default => $key,
        };
    }
}
