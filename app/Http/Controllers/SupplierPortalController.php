<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class SupplierPortalController extends Controller
{
    public function index(): View
    {
        return view('dashboards.supplier');
    }

    public function contact(): View
    {
        return view('dashboards.supplier-contact');
    }
}
