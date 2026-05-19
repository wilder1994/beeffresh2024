<?php

declare(strict_types=1);

namespace App\Support;

final class AuthLoginAudience
{
    public const CLIENT = 'cliente';

    public const EMPLOYEE = 'empleado';

    public const SUPPLIER = 'proveedor';

    /** @var list<string> */
    private const ALL = [self::CLIENT, self::EMPLOYEE, self::SUPPLIER];

    public static function resolve(?string $value): string
    {
        $normalized = $value !== null ? strtolower(trim($value)) : self::CLIENT;

        return in_array($normalized, self::ALL, true) ? $normalized : self::CLIENT;
    }

    public static function label(string $audience): string
    {
        return match ($audience) {
            self::EMPLOYEE => 'Empleado',
            self::SUPPLIER => 'Proveedor',
            default => 'Cliente',
        };
    }

    public static function heading(string $audience): string
    {
        return match ($audience) {
            self::EMPLOYEE => 'Ingreso — personal interno',
            self::SUPPLIER => 'Ingreso — proveedor',
            default => 'Ingreso — cliente',
        };
    }

    public static function description(string $audience): string
    {
        return match ($audience) {
            self::EMPLOYEE => 'Usa el correo y la contraseña que te asignó la empresa. Si aún no tienes acceso, contacta al administrador.',
            self::SUPPLIER => 'Usa el correo y la contraseña que te asignó la empresa. Los proveedores no se registran por su cuenta.',
            default => 'Compra en la tienda en línea con tu cuenta de cliente. Si aún no tienes una, puedes registrarte al final de este formulario.',
        };
    }

    public static function allowsSelfRegistration(string $audience): bool
    {
        return $audience === self::CLIENT;
    }

    /**
     * @return array<string, string>
     */
    public static function loginRouteOptions(): array
    {
        return [
            self::CLIENT => route('login', ['tipo' => self::CLIENT]),
            self::EMPLOYEE => route('login', ['tipo' => self::EMPLOYEE]),
            self::SUPPLIER => route('login', ['tipo' => self::SUPPLIER]),
        ];
    }
}
