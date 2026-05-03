<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use Illuminate\Contracts\View\View;

class NosotrosController extends Controller
{
    public function __invoke(): View
    {
        $profile = CompanyProfile::singleton();

        return view('nosotros', ['profile' => $profile]);
    }
}
