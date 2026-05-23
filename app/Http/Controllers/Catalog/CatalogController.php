<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class CatalogController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('catalog.products.index');
    }
}
