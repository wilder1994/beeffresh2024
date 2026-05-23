<?php

declare(strict_types=1);

namespace App\Domain\Geo;

/**
 * Ciudades y barrios por departamento (Colombia) para selects en cascada.
 *
 * @phpstan-type LocationEntry array{cities: list<string>, neighborhoods: array<string, list<string>>}
 */
final class ColombianLocations
{
    /** @var array<string, LocationEntry>|null */
    private static ?array $data = null;

    /**
     * @return array<string, LocationEntry>
     */
    public static function all(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $path = resource_path('data/colombia-locations.json');
        $decoded = is_readable($path)
            ? json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR)
            : [];

        /** @var array<string, LocationEntry> $merged */
        $merged = is_array($decoded) ? $decoded : [];

        foreach (ColombianDepartments::names() as $department) {
            if (! isset($merged[$department])) {
                $merged[$department] = ['cities' => [], 'neighborhoods' => []];
            }
            if ($merged[$department]['cities'] === []) {
                $capital = self::capitalFor($department);
                if ($capital !== null) {
                    $merged[$department]['cities'] = [$capital];
                }
            }
        }

        self::$data = $merged;

        return self::$data;
    }

    /**
     * @return list<string>
     */
    public static function citiesForDepartment(?string $department): array
    {
        if ($department === null || trim($department) === '') {
            return [];
        }

        $entry = self::all()[trim($department)] ?? null;

        return $entry['cities'] ?? [];
    }

    /**
     * @return list<string>
     */
    public static function neighborhoodsFor(?string $department, ?string $city): array
    {
        if ($department === null || $city === null) {
            return [];
        }

        $entry = self::all()[trim($department)] ?? null;
        if ($entry === null) {
            return [];
        }

        return $entry['neighborhoods'][trim($city)] ?? [];
    }

    public static function capitalFor(string $department): ?string
    {
        return match ($department) {
            'Amazonas' => 'Leticia',
            'Antioquia' => 'Medellín',
            'Arauca' => 'Arauca',
            'Atlántico' => 'Barranquilla',
            'Bolívar' => 'Cartagena',
            'Boyacá' => 'Tunja',
            'Caldas' => 'Manizales',
            'Caquetá' => 'Florencia',
            'Casanare' => 'Yopal',
            'Cauca' => 'Popayán',
            'Cesar' => 'Valledupar',
            'Chocó' => 'Quibdó',
            'Córdoba' => 'Montería',
            'Cundinamarca' => 'Soacha',
            'Guainía' => 'Inírida',
            'Guaviare' => 'San José del Guaviare',
            'Huila' => 'Neiva',
            'La Guajira' => 'Riohacha',
            'Magdalena' => 'Santa Marta',
            'Meta' => 'Villavicencio',
            'Nariño' => 'Pasto',
            'Norte de Santander' => 'Cúcuta',
            'Putumayo' => 'Mocoa',
            'Quindío' => 'Armenia',
            'Risaralda' => 'Pereira',
            'San Andrés y Providencia' => 'San Andrés',
            'Santander' => 'Bucaramanga',
            'Sucre' => 'Sincelejo',
            'Tolima' => 'Ibagué',
            'Valle del Cauca' => 'Cali',
            'Vaupés' => 'Mitú',
            'Vichada' => 'Puerto Carreño',
            'Bogotá D.C.' => 'Bogotá',
            default => null,
        };
    }
}
