<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** @deprecated Rutas legacy; redirigen a configuración de empresa. */
class CompanyProfileController extends Controller
{
    public function edit(Request $request): RedirectResponse
    {
        return redirect()->route('admin.configuracion.empresa', [
            'tab' => 'nosotros',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        return redirect()->route('admin.configuracion.empresa', [
            'tab' => 'nosotros',
        ]);
    }
}
