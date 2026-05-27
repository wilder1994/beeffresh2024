<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCompanyAboutRequest;
use App\Http\Requests\Admin\UpdateCompanyGeneralRequest;
use App\Http\Requests\Admin\UpdateCompanyLocationRequest;
use App\Models\CompanyProfile;
use App\Services\Admin\CompanySettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanySettingsController extends Controller
{
    public const TAB_GENERAL = 'general';

    public const TAB_LOCATION = 'ubicacion';

    public const TAB_ABOUT = 'nosotros';

    public function __construct(
        private readonly CompanySettingsService $companySettings,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $tab = $this->resolveTab($request->query('tab'));

        return view('admin.configuracion.empresa.index', [
            'profile' => CompanyProfile::singleton(),
            'tab' => $tab,
            'logoUrl' => CompanySettingsService::principalLogoUrl(),
        ]);
    }

    public function updateGeneral(UpdateCompanyGeneralRequest $request): RedirectResponse
    {
        $profile = CompanyProfile::singleton();
        $this->companySettings->updateGeneral(
            $profile,
            $request->validated(),
            $request->file('logo'),
        );

        return $this->redirectToTab(self::TAB_GENERAL, 'Datos generales y logo guardados.');
    }

    public function updateLocation(UpdateCompanyLocationRequest $request): RedirectResponse
    {
        $profile = CompanyProfile::singleton();
        $this->companySettings->updateLocation($profile, $request->locationAttributes());

        return $this->redirectToTab(self::TAB_LOCATION, 'Ubicación de la tienda guardada.');
    }

    public function updateAbout(UpdateCompanyAboutRequest $request): RedirectResponse
    {
        $profile = CompanyProfile::singleton();
        $this->companySettings->updateAbout($profile, $request->validated());

        return $this->redirectToTab(self::TAB_ABOUT, 'Contenido de la página Nosotros guardado.');
    }

    private function resolveTab(mixed $tab): string
    {
        $tab = is_string($tab) ? $tab : self::TAB_GENERAL;

        return in_array($tab, [self::TAB_GENERAL, self::TAB_LOCATION, self::TAB_ABOUT], true)
            ? $tab
            : self::TAB_GENERAL;
    }

    private function redirectToTab(string $tab, string $message): RedirectResponse
    {
        return redirect()
            ->route('admin.configuracion.empresa', ['tab' => $tab])
            ->with('success', $message);
    }
}
