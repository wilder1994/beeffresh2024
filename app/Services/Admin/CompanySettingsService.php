<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\CompanyProfile;
use App\Models\Logo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class CompanySettingsService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateGeneral(CompanyProfile $profile, array $data, ?UploadedFile $logo = null): void
    {
        $profile->update([
            'legal_name' => $data['legal_name'] ?? null,
            'trade_name' => $data['trade_name'] ?? null,
            'nit' => $data['nit'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
        ]);

        if ($logo !== null) {
            $this->replacePrincipalLogo($logo);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateLocation(CompanyProfile $profile, array $data): void
    {
        $profile->update([
            'store_address' => $data['store_address'] ?? null,
            'store_neighborhood' => $data['store_neighborhood'] ?? null,
            'store_city' => $data['store_city'] ?? null,
            'store_state' => $data['store_state'] ?? null,
            'store_latitude' => $data['store_latitude'],
            'store_longitude' => $data['store_longitude'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAbout(CompanyProfile $profile, array $data): void
    {
        $profile->update($data);
    }

    public function replacePrincipalLogo(UploadedFile $file): Logo
    {
        $logo = Logo::query()->firstOrNew(['tipo' => 'principal']);

        if ($logo->imagen) {
            Storage::disk('public')->delete('logos/'.$logo->imagen);
        }

        $nombreImagen = time().'.'.$file->extension();
        $file->storeAs('logos', $nombreImagen, 'public');
        $logo->imagen = $nombreImagen;
        $logo->save();

        return $logo;
    }

    public static function principalLogoUrl(): string
    {
        $logo = Logo::query()->where('tipo', 'principal')->first();

        if ($logo !== null && $logo->imagen) {
            return asset('storage/logos/'.$logo->imagen);
        }

        return asset('logos/logo.jpeg');
    }
}
