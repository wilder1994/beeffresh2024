<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CompanySettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoController extends Controller
{
    public function __construct(
        private readonly CompanySettingsService $companySettings,
    ) {}

    /** Logo de la empresa (tipo principal); atajo desde sidebar. */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $logo = $this->companySettings->replacePrincipalLogo($request->file('imagen'));

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'url' => asset('storage/logos/'.$logo->imagen)]);
        }

        return redirect()->back()->with('success', 'Logo de la empresa actualizado.');
    }
}
