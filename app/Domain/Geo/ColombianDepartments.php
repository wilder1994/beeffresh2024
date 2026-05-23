<?php

declare(strict_types=1);

namespace App\Domain\Geo;

/**
 * Departamentos de Colombia (incluye Bogotá D.C.).
 * El valor almacenado en BD es el nombre completo del departamento.
 */
final class ColombianDepartments
{
    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return [
            'Amazonas',
            'Antioquia',
            'Arauca',
            'Atlántico',
            'Bolívar',
            'Boyacá',
            'Caldas',
            'Caquetá',
            'Casanare',
            'Cauca',
            'Cesar',
            'Chocó',
            'Córdoba',
            'Cundinamarca',
            'Guainía',
            'Guaviare',
            'Huila',
            'La Guajira',
            'Magdalena',
            'Meta',
            'Nariño',
            'Norte de Santander',
            'Putumayo',
            'Quindío',
            'Risaralda',
            'San Andrés y Providencia',
            'Santander',
            'Sucre',
            'Tolima',
            'Valle del Cauca',
            'Vaupés',
            'Vichada',
            'Bogotá D.C.',
        ];
    }

    public static function isKnown(?string $name): bool
    {
        if ($name === null || trim($name) === '') {
            return false;
        }

        return in_array(trim($name), self::names(), true);
    }
}
