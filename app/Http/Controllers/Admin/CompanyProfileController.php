<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCompanyProfileRequest;
use App\Models\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function edit(): View
    {
        $profile = CompanyProfile::singleton();

        return view('admin.empresa.edit', ['profile' => $profile]);
    }

    public function update(UpdateCompanyProfileRequest $request): RedirectResponse
    {
        $profile = CompanyProfile::singleton();
        $data = $request->validated();
        foreach (['social_facebook', 'social_instagram', 'social_twitter', 'social_whatsapp', 'social_tiktok', 'social_youtube'] as $urlKey) {
            if (isset($data[$urlKey]) && $data[$urlKey] === '') {
                $data[$urlKey] = null;
            }
        }
        $profile->update($data);

        return redirect()
            ->route('admin.empresa.edit')
            ->with('success', 'Contenido de la página «Nosotros» actualizado.');
    }
}
