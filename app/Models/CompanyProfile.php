<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    public const SINGLETON_ID = 1;

    protected $fillable = [
        'about_heading',
        'about_content',
        'promise_heading',
        'promise_content',
        'social_heading',
        'social_facebook',
        'social_instagram',
        'social_twitter',
        'social_youtube',
    ];

    public static function singleton(): self
    {
        $row = static::query()->find(self::SINGLETON_ID);
        if ($row === null) {
            throw new \RuntimeException('Falta el registro de perfil de empresa. Ejecute las migraciones.');
        }

        return $row;
    }
}
