<?php

declare(strict_types=1);

namespace App\Domain\Users;

use Illuminate\Validation\Rule;

/**
 * Tipos de identificación usados en Colombia (códigos almacenados en users.document_type).
 */
final class ColombianDocumentType
{
    public const CC = 'CC';

    public const CE = 'CE';

    public const TI = 'TI';

    public const PA = 'PA';

    public const PEP = 'PEP';

    public const PPT = 'PPT';

    public const NIT = 'NIT';

    public const RC = 'RC';

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return [
            self::CC,
            self::CE,
            self::TI,
            self::PA,
            self::PEP,
            self::PPT,
            self::NIT,
            self::RC,
        ];
    }

    /**
     * @return array<string, string> code => label
     */
    public static function options(): array
    {
        return [
            self::CC => 'Cédula de ciudadanía',
            self::CE => 'Cédula de extranjería',
            self::TI => 'Tarjeta de identidad',
            self::PA => 'Pasaporte',
            self::PEP => 'Permiso especial de permanencia (PEP)',
            self::PPT => 'Permiso por protección temporal (PPT)',
            self::NIT => 'NIT',
            self::RC => 'Registro civil',
        ];
    }

    public static function label(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        $normalized = strtoupper(trim($code));

        return self::options()[$normalized] ?? $code;
    }

    public static function isKnown(?string $code): bool
    {
        if ($code === null || $code === '') {
            return false;
        }

        return in_array(strtoupper(trim($code)), self::codes(), true);
    }

    /**
     * @return list<\Illuminate\Contracts\Validation\Rule|string>
     */
    public static function validationRules(bool $required = false): array
    {
        $rules = ['string', Rule::in(self::codes())];

        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }
}
